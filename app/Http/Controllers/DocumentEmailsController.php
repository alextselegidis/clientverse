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
use App\Notifications\DocumentEmail;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Emails a document's customer link to the customer. A draft becomes "sent"
 * once the email has gone out, since drafts have no public page.
 */
class DocumentEmailsController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        return $this->send($invoice->customer, 'invoice', [
            'number' => $invoice->number,
            'total' => format_currency($invoice->total, $invoice->currency),
            'due_date' => $invoice->due_date?->format('M d, Y') ?? '-',
            'link' => $invoice->publicUrl(),
        ], fn () => $invoice->status === 'draft' && $invoice->update(['status' => 'sent']));
    }

    public function estimate(Estimate $estimate)
    {
        return $this->send($estimate->customer, 'estimate', [
            'number' => $estimate->number,
            'total' => format_currency($estimate->total, $estimate->currency),
            'link' => $estimate->publicUrl(),
        ], fn () => $estimate->status === 'draft' && $estimate->update(['status' => 'sent']));
    }

    public function contract(Contract $contract)
    {
        return $this->send($contract->customer, 'contract', [
            'title' => $contract->title,
            'link' => $contract->signingUrl(),
        ]);
    }

    private function send(?Customer $customer, string $type, array $values, ?callable $afterSending = null)
    {
        if (blank($customer?->email)) {
            return back()->with('error', __('customer_has_no_email_message'));
        }

        $template = email_template($type, $values + [
            'customer' => $customer->name,
            'company' => setting('company_name') ?: config('app.name'),
        ]);

        try {
            Notification::route('mail', $customer->email)->notify(new DocumentEmail($template['subject'], $template['body']));
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
