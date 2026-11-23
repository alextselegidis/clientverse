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

use App\Models\Concerns\HasDocumentNumber;
use App\Models\Concerns\HasLineItems;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Invoice extends Model
{
    use HasDocumentNumber;
    use HasLineItems;
    use RecordsActivity;
    use SoftDeletes;

    public const NUMBER_PREFIX = 'INV';

    protected static function booted(): void
    {
        // A deleted invoice releases its credit notes so they can be applied elsewhere.
        static::deleted(fn (Invoice $invoice) => CreditNote::where('invoice_id', $invoice->id)
            ->get()
            ->each(fn (CreditNote $creditNote) => $creditNote->invoice()->dissociate()->saveQuietly()));
    }

    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'recurring_interval',
        'next_recurring_date',
        'currency',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'next_recurring_date' => 'date',
        'reminded_at' => 'datetime',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * A permanent link the customer can open without an account.
     */
    public function publicUrl(): string
    {
        return URL::signedRoute('public.invoices.show', ['invoice' => $this->id]);
    }

    /**
     * The {placeholders} the invoice email templates fill in.
     */
    public function emailValues(): array
    {
        return [
            'number' => $this->number,
            'total' => format_currency($this->total, $this->currency),
            'balance' => format_currency($this->balance, $this->currency),
            'due_date' => $this->due_date?->format('M d, Y') ?? '-',
            'link' => $this->publicUrl(),
        ];
    }

    /**
     * The online payment providers an admin has set up under Setup > Billing.
     */
    public static function paymentGateways(): array
    {
        return array_keys(array_filter([
            'stripe' => filled(setting('stripe_secret_key')),
            'paypal' => filled(setting('paypal_client_id')) && filled(setting('paypal_secret')),
        ]));
    }

    /**
     * Whether the customer can pay the balance online, through $gateway or any.
     */
    public function canBePaidOnline(?string $gateway = null): bool
    {
        $gateways = self::paymentGateways();

        return $this->status === 'sent' && $this->balance > 0
            && ($gateway ? in_array($gateway, $gateways, true) : $gateways !== []);
    }

    /**
     * Record money a payment provider confirmed. Providers retry and customers
     * reload, so a payment already recorded under this reference is skipped. A
     * deleted invoice still gets the record: the money did arrive.
     */
    public static function recordOnlinePayment(
        int|string $invoiceId,
        float $amount,
        string $method,
        string $reference,
    ): void {
        DB::transaction(function () use ($invoiceId, $amount, $method, $reference) {
            $invoice = self::withTrashed()->whereKey($invoiceId)->lockForUpdate()->first();

            if (!$invoice || $invoice->payments()->where('reference', $reference)->exists()) {
                return;
            }

            $invoice->payments()->create([
                'amount' => round($amount, 2),
                'paid_on' => today(),
                'method' => $method,
                'reference' => $reference,
            ]);
            $invoice->recalculate();
        });
    }

    public static function recurringIntervals(): array
    {
        return [
            'week' => __('weekly'),
            'month' => __('monthly'),
            'quarter' => __('quarterly'),
            'year' => __('yearly'),
        ];
    }

    /**
     * Copy a recurring invoice once for every scheduled date that has come, each
     * copy a sent invoice dated on its day with the template's payment terms.
     * Only sent invoices recur, so a draft or cancelled one is paused. The row
     * lock stops two overlapping runs from billing the same date twice.
     *
     * @return Invoice[]
     */
    public function createDueCopies(): array
    {
        return DB::transaction(function () {
            $template = self::whereKey($this->id)->lockForUpdate()->firstOrFail()->load('items');
            $paymentDays = $template->issue_date && $template->due_date
                ? (int) abs($template->issue_date->diffInDays($template->due_date))
                : 30;
            $copies = [];

            while ($template->status === 'sent' && $template->recurring_interval
                && $template->next_recurring_date?->lte(today())) {
                $issueDate = $template->next_recurring_date->copy();

                $copy = self::create([
                    'customer_id' => $template->customer_id,
                    'status' => 'sent',
                    'issue_date' => $issueDate,
                    'due_date' => $issueDate->copy()->addDays($paymentDays),
                    'currency' => $template->currency,
                    'notes' => $template->notes,
                    'terms' => $template->terms,
                ]);
                $copy->recurring_parent_id = $template->id;
                $copy->syncItems($template->itemRows());

                // ponytail: steps from the last date, so the 31st drifts to the 28th after February.
                $template->next_recurring_date = match ($template->recurring_interval) {
                    'week' => $issueDate->addWeek(),
                    'quarter' => $issueDate->addMonthsNoOverflow(3),
                    'year' => $issueDate->addYearNoOverflow(),
                    default => $issueDate->addMonthNoOverflow(),
                };
                $copies[] = $copy;
            }

            $template->save();

            return $copies;
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class)->where('status', 'issued');
    }

    /**
     * The part of amount_paid that came from credit notes rather than payments.
     */
    public function getCreditedAttribute(): float
    {
        return round((float) $this->creditNotes->sum('total'), 2);
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    /**
     * Refresh the cached total and amount settled. amount_paid counts payments
     * and issued credit notes alike, since both reduce what the customer owes.
     */
    public function recalculate(): void
    {
        $this->load('items');

        $this->total = round($this->subtotal + $this->tax, 2);
        $this->amount_paid = round((float) $this->payments()->sum('amount') + (float) $this->creditNotes()->sum('total'), 2);
        $this->save();
    }

    /**
     * The status people care about: what was stored for draft and cancelled
     * invoices, otherwise how far the invoice has been paid.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status !== 'sent') {
            return $this->status;
        }

        if ((float) $this->total > 0 && $this->balance <= 0) {
            return 'paid';
        }

        if ($this->balance > 0 && $this->due_date && $this->due_date->lt(today())) {
            return 'overdue';
        }

        return (float) $this->amount_paid > 0 ? 'partially_paid' : 'unpaid';
    }

    /**
     * The SQL twin of getDisplayStatusAttribute(); keep the two in step.
     */
    public function scopeWhereDisplayStatus($query, ?string $status)
    {
        $isPaid = fn ($query) => $query->where('total', '>', 0)->whereColumn('amount_paid', '>=', 'total');
        $isOverdue = fn ($query) => $query->whereColumn('amount_paid', '<', 'total')->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());

        return match ($status) {
            'draft', 'cancelled' => $query->where('status', $status),
            'paid' => $query->where('status', 'sent')->where($isPaid),
            'overdue' => $query->where('status', 'sent')->where($isOverdue),
            'partially_paid' => $query->where('status', 'sent')->where('amount_paid', '>', 0)
                ->whereNot($isPaid)->whereNot($isOverdue),
            'unpaid' => $query->where('status', 'sent')->where('amount_paid', 0)
                ->whereNot($isPaid)->whereNot($isOverdue),
            default => $query,
        };
    }

    public static function displayStatuses(): array
    {
        return [
            'draft' => __('draft'),
            'unpaid' => __('unpaid'),
            'partially_paid' => __('partially_paid'),
            'paid' => __('paid'),
            'overdue' => __('overdue'),
            'cancelled' => __('cancelled'),
        ];
    }

    public static function statusColors(): array
    {
        return [
            'draft' => 'secondary',
            'unpaid' => 'info',
            'partially_paid' => 'warning',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
        ];
    }
}
