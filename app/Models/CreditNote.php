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

class CreditNote extends Model
{
    use HasDocumentNumber;
    use HasLineItems;
    use RecordsActivity;
    use SoftDeletes;

    public const NUMBER_PREFIX = 'CN';

    protected $fillable = ['customer_id', 'number', 'status', 'issue_date', 'currency', 'notes'];

    protected $casts = [
        'issue_date' => 'date',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // A credit's total or status feeds its invoice's balance.
        static::saved(fn (CreditNote $creditNote) => $creditNote->refreshInvoices());
        static::deleted(fn (CreditNote $creditNote) => $creditNote->refreshInvoices());
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class)->orderBy('position');
    }

    /**
     * Recalculate the invoice this credit is on, and the one it just left.
     */
    private function refreshInvoices(): void
    {
        foreach (array_unique(array_filter([$this->invoice_id, $this->getOriginal('invoice_id')])) as $id) {
            Invoice::find($id)?->recalculate();
        }
    }

    public static function statuses(): array
    {
        return ['draft' => __('draft'), 'issued' => __('issued')];
    }

    public static function statusColors(): array
    {
        return ['draft' => 'secondary', 'issued' => 'success'];
    }
}
