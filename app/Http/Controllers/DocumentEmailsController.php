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

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\Subscription;
use App\Notifications\DocumentEmail;
use Throwable;

/**
 * Emails a document's customer link to the customer. A draft becomes "sent"
 * once the email has gone out, since drafts have no public page.
 */
class DocumentEmailsController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        return $this->send($invoice->customer, 'invoice', $invoice->emailValues(), fn () => $invoice->status === 'draft' && $invoice->update(['status' => 'sent']));
    }

    public function estimate(Estimate $estimate)
    {
        return $this->send($estimate->customer, 'estimate', [
            'number' => $estimate->number,
            'total' => format_currency($estimate->total, $estimate->currency),
            'link' => $estimate->publicUrl(),
        ], fn () => $estimate->status === 'draft' && $estimate->update(['status' => 'sent']));
    }

    public function proposal(Proposal $proposal)
    {
        return $this->send($proposal->customer, 'proposal', [
            'number' => $proposal->number,
            'subject' => $proposal->subject,
            'total' => format_currency($proposal->total, $proposal->currency),
            'link' => $proposal->publicUrl(),
        ], fn () => $proposal->status === 'draft' && $proposal->update(['status' => 'sent']));
    }

    public function contract(Contract $contract)
    {
        return $this->send($contract->customer, 'contract', [
            'title' => $contract->title,
            'link' => $contract->signingUrl(),
        ]);
    }

    public function subscription(Subscription $subscription)
    {
        abort_unless($subscription->isEditable(), 403);

        return $this->send($subscription->customer, 'subscription', $subscription->emailValues(),
            fn () => $subscription->status === 'draft' && $subscription->forceFill(['status' => 'pending'])->save());
    }

    private function send(?Customer $customer, string $type, array $values, ?callable $afterSending = null)
    {
        try {
            if (!DocumentEmail::sendTemplate($customer, $type, $values)) {
                return back()->with('error', __('customer_has_no_email_message'));
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', __('email_failed_message'));
        }

        if ($afterSending) {
            $afterSending();
        }

        return back()->with('success', __('document_emailed_message', ['email' => $customer->email]));
    }
}
