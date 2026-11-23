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

use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\ContractRenewalDue;
use App\Notifications\DocumentEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Both run from the scheduler: `* * * * * php artisan schedule:run` in the server's crontab.
Schedule::command('invoices:recur')->dailyAt('06:00');
Schedule::command('reminders:send')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('model:prune')->dailyAt('03:00');

Artisan::command('invoices:recur', function () {
    Invoice::where('status', 'sent')
        ->whereNotNull('recurring_interval')
        ->whereDate('next_recurring_date', '<=', today())
        ->lazyById()->each(function (Invoice $invoice) {
            foreach ($invoice->createDueCopies() as $copy) {
                try {
                    DocumentEmail::sendTemplate($copy->customer, 'invoice', $copy->emailValues());
                } catch (Throwable $exception) {
                    // The invoice exists either way; the customer can still open it in the portal.
                    report($exception);
                }

                $this->info("Created {$copy->number} from {$invoice->number}");
            }
        });
})->purpose('Create the invoices that recurring invoices are due to produce');

Artisan::command('reminders:send', function () {
    // Staff hear about recurring contracts 30 and 7 days before they end, whatever the customer
    // reminder setting says. Matching the exact day keeps it to two emails without a sent flag.
    $staff = User::where('is_active', true)->get()->filter(fn (User $user) => $user->hasPermission('contracts'));
    Contract::with('customer')->where('status', 'active')->where('type', 'recurring')
        ->where(fn ($query) => $query->whereDate('end_date', today()->addDays(30))->orWhereDate('end_date', today()->addDays(7)))
        ->lazyById()->each(function (Contract $contract) use ($staff) {
            try {
                Notification::send($staff, new ContractRenewalDue($contract));
                $this->info("Sent renewal notice for contract #{$contract->id}");
            } catch (Throwable $exception) {
                report($exception);
            }
        });

    if (!setting('send_reminders')) {
        $this->info('Reminders are switched off under Setup > Billing.');

        return;
    }

    $remind = function ($record, string $type, array $values) {
        try {
            if (DocumentEmail::sendTemplate($record->customer, $type, $values)) {
                // Quietly: the date-change hooks must not see this save.
                $record->forceFill(['reminded_at' => now()])->saveQuietly();
                $this->info("Sent {$type} for #{$record->id}");
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    };
    // lazyById, not each(): every reminder drops its row out of the query, which shifts each()'s pages.
    $soon = fn (int $days) => [today()->toDateString(), today()->addDays($days)->toDateString()];

    // Overdue invoices: once a week until paid.
    Invoice::with('customer')
        ->whereDisplayStatus('overdue')
        ->where(fn ($query) => $query->whereNull('reminded_at')->orWhere('reminded_at', '<=', now()->subDays(7)))
        ->lazyById()->each(fn (Invoice $invoice) => $remind($invoice, 'invoice_overdue', $invoice->emailValues()));

    // Estimates and proposals still awaiting an answer, once, three days before they lapse.
    Estimate::with('customer')->where('status', 'sent')->whereNull('reminded_at')
        ->whereBetween('expiry_date', $soon(3))
        ->lazyById()->each(fn (Estimate $estimate) => $remind($estimate, 'estimate_expiring', [
            'number' => $estimate->number,
            'date' => $estimate->expiry_date->format('M d, Y'),
            'link' => $estimate->publicUrl(),
        ]));

    Proposal::with('customer')->where('status', 'sent')->whereNull('reminded_at')
        ->whereBetween('open_till', $soon(3))
        ->lazyById()->each(fn (Proposal $proposal) => $remind($proposal, 'proposal_expiring', [
            'number' => $proposal->number,
            'date' => $proposal->open_till->format('M d, Y'),
            'link' => $proposal->publicUrl(),
        ]));

    // Active contracts, once, a week before they end.
    Contract::with('customer')->where('status', 'active')->whereNull('reminded_at')
        ->whereBetween('end_date', $soon(7))
        ->lazyById()->each(fn (Contract $contract) => $remind($contract, 'contract_expiring', [
            'title' => $contract->title,
            'date' => $contract->end_date->format('M d, Y'),
            'link' => $contract->signingUrl(),
        ]));
})->purpose('Email staff about recurring contracts up for renewal, and customers about overdue invoices and expiring documents');
