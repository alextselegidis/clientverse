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
use Illuminate\Http\Request;

/**
 * Customer-facing invoices and estimates, reached through signed links. Drafts
 * stay private until they are sent.
 */
class PublicDocumentsController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        abort_if($invoice->status === 'draft', 404);

        return view('pages.document-print', [
            'document' => $invoice->load('customer', 'items', 'creditNotes'),
            'title' => __('invoice'),
            'backUrl' => null,
            'dates' => [__('issue_date') => $invoice->issue_date, __('due_date') => $invoice->due_date],
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
        abort_unless($estimate->status === 'sent', 404);

        $data = $request->validate(['decision' => 'required|in:accepted,declined']);

        $estimate->update(['status' => $data['decision']]);

        return back()->with('success', __('estimate_' . $data['decision'] . '_message'));
    }
}
