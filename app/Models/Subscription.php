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

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * A fixed price the customer pays every week, month or year. Stripe collects
 * it: the customer subscribes on a public page, and every paid period Stripe
 * reports through the webhook is booked here as a paid invoice.
 */
class Subscription extends Model
{
    use RecordsActivity;

    protected $fillable = ['customer_id', 'name', 'description', 'currency', 'amount', 'tax_rate', 'interval'];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'current_period_end' => 'date',
        'canceled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->latest('issue_date')->latest('id');
    }

    /**
     * The price per period including tax: what Stripe charges.
     */
    public function getGrossAmountAttribute(): float
    {
        return round((float) $this->amount * (1 + (float) $this->tax_rate / 100), 2);
    }

    /**
     * Price and terms are fixed once the customer has subscribed.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending'], true);
    }

    public function canBeSubscribedTo(): bool
    {
        return $this->isEditable() && filled(setting('stripe_secret_key'));
    }

    public function publicUrl(): string
    {
        return URL::signedRoute('public.subscriptions.show', ['subscription' => $this->id]);
    }

    public function emailValues(): array
    {
        return [
            'name' => $this->name,
            'total' => format_currency($this->gross_amount, $this->currency),
            'interval' => self::intervals()[$this->interval],
            'link' => $this->publicUrl(),
        ];
    }

    /**
     * Book a paid Stripe invoice as a paid invoice of ours. Stripe retries
     * webhooks, so an invoice already booked under its Stripe id is skipped;
     * the row lock keeps two deliveries of the same event from both booking it.
     */
    public function recordStripeInvoice(array $stripeInvoice, float $amountPaid): void
    {
        DB::transaction(function () use ($stripeInvoice, $amountPaid) {
            $subscription = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (Payment::where('reference', $stripeInvoice['id'])->exists()) {
                return;
            }

            $periodEnd = $stripeInvoice['lines']['data'][0]['period']['end'] ?? null;

            $invoice = Invoice::create([
                'customer_id' => $subscription->customer_id,
                'status' => 'sent',
                'issue_date' => today(),
                'due_date' => today(),
                'currency' => $subscription->currency,
            ]);
            $invoice->subscription_id = $subscription->id;
            $invoice->syncItems([[
                'description' => $subscription->name,
                'quantity' => 1,
                'unit_price' => $subscription->amount,
                'tax_rate' => $subscription->tax_rate,
            ]]);

            Invoice::recordOnlinePayment($invoice->id, $amountPaid, 'Stripe', $stripeInvoice['id']);

            $subscription->status = 'active';
            $subscription->current_period_end = $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null;
            $subscription->save();
        });
    }

    /**
     * Follow a status change Stripe reports for the subscription.
     */
    public function syncStripeStatus(string $stripeStatus): void
    {
        $status = match ($stripeStatus) {
            'active', 'trialing' => 'active',
            'past_due', 'unpaid' => 'past_due',
            'canceled', 'incomplete_expired' => 'canceled',
            default => null,
        };

        if ($status && $status !== $this->status) {
            $this->status = $status;
            $this->canceled_at = $status === 'canceled' ? ($this->canceled_at ?? now()) : $this->canceled_at;
            $this->save();
        }
    }

    public static function intervals(): array
    {
        return ['week' => __('weekly'), 'month' => __('monthly'), 'year' => __('yearly')];
    }

    public static function statuses(): array
    {
        return [
            'draft' => __('draft'),
            'pending' => __('awaiting_customer'),
            'active' => __('active'),
            'past_due' => __('past_due'),
            'canceled' => __('cancelled'),
        ];
    }

    public static function statusColors(): array
    {
        return [
            'draft' => 'secondary', 'pending' => 'info', 'active' => 'success', 'past_due' => 'danger', 'canceled' => 'secondary',
        ];
    }
}
