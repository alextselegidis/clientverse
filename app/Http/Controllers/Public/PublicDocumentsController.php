<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Customer-facing invoices, estimates and proposals, reached through signed
 * links. Drafts stay private until they are sent.
 */
class PublicDocumentsController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        abort_if($invoice->status === 'draft', 404);

        $payUrl = fn (string $gateway, string $route) => $invoice->canBePaidOnline($gateway)
            ? URL::signedRoute($route, ['invoice' => $invoice->id])
            : null;

        return view('pages.document-print', [
            'document' => $invoice->load('customer', 'items', 'creditNotes'),
            'title' => __('invoice'),
            'backUrl' => null,
            'dates' => [__('issue_date') => $invoice->issue_date, __('due_date') => $invoice->due_date],
            'payUrls' => [
                'stripe' => $payUrl('stripe', 'public.invoices.pay'),
                'paypal' => $payUrl('paypal', 'public.invoices.paypal'),
            ],
        ]);
    }

    public function estimate(Request $request, Estimate $estimate)
    {
        abort_if($estimate->status === 'draft', 404);

        return view('pages.document-print', [
            'document' => $estimate->load('customer', 'items'),
            'title' => __('estimate'),
            'backUrl' => null,
            'dates' => [__('issue_date') => $estimate->issue_date, __('expiry_date') => $estimate->expiry_date],
            'respondUrl' => $estimate->status === 'sent' ? $request->fullUrl() : null,
        ]);
    }

    public function respondToEstimate(Request $request, Estimate $estimate)
    {
        return $this->respond($request, $estimate, 'estimate');
    }

    public function proposal(Request $request, Proposal $proposal)
    {
        abort_if($proposal->status === 'draft', 404);

        return view('pages.document-print', [
            'document' => $proposal->load('customer', 'items'),
            'title' => __('proposal'),
            'backUrl' => null,
            'dates' => [__('issue_date') => $proposal->issue_date, __('open_till') => $proposal->open_till],
            'respondUrl' => $proposal->status === 'sent' ? $request->fullUrl() : null,
            'acceptLabel' => __('accept_proposal'),
        ]);
    }

    public function respondToProposal(Request $request, Proposal $proposal)
    {
        return $this->respond($request, $proposal, 'proposal');
    }

    // Only a sent document can be accepted or declined, and only once.
    private function respond(Request $request, Estimate|Proposal $document, string $type)
    {
        abort_unless($document->status === 'sent', 404);

        $data = $request->validate(['decision' => 'required|in:accepted,declined']);

        $document->update(['status' => $data['decision']]);

        return back()->with('success', __($type . '_' . $data['decision'] . '_message'));
    }
}
