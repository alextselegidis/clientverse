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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'currency',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // The id is only known after the insert, so a blank number is filled in afterwards.
        static::created(function (Invoice $invoice) {
            if (empty($invoice->number)) {
                $invoice->number = sprintf('INV-%06d', $invoice->id);
                $invoice->saveQuietly();
            }
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

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn (InvoiceItem $item) => $item->subtotal);
    }

    public function getTaxAttribute(): float
    {
        return $this->items->sum(fn (InvoiceItem $item) => $item->tax);
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    /**
     * Replace every line with the given rows. Rows without a description are
     * dropped, which is how a line is removed from the form.
     */
    public function syncItems(array $rows): void
    {
        $this->items()->delete();

        $position = 0;

        foreach ($rows as $row) {
            if (blank($row['description'] ?? null)) {
                continue;
            }

            $this->items()->create([
                'description' => $row['description'],
                'quantity' => $row['quantity'] ?? 1,
                'unit_price' => $row['unit_price'] ?? 0,
                'tax_rate' => $row['tax_rate'] ?? 0,
                'position' => $position++,
            ]);
        }

        $this->recalculate();
    }

    /**
     * Refresh the cached total and amount paid from the lines and payments.
     */
    public function recalculate(): void
    {
        $this->load('items');

        $this->total = round($this->subtotal + $this->tax, 2);
        $this->amount_paid = (float) $this->payments()->sum('amount');
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
