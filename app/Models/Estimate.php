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

class Estimate extends Model
{
    use HasDocumentNumber;
    use HasLineItems;
    use RecordsActivity;
    use SoftDeletes;

    public const NUMBER_PREFIX = 'EST';

    protected static function booted(): void
    {
        // A new expiry_date earns a new reminder.
        // Written straight to the row: a model loaded before the reminder went out
        // still holds reminded_at = null, so setting it on the model would save nothing.
        static::updated(function (self $model) {
            if ($model->wasChanged('expiry_date')) {
                $model->newQuery()->whereKey($model->getKey())->toBase()->update(['reminded_at' => null]);
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'issue_date',
        'expiry_date',
        'currency',
        'notes',
        'terms',
    ];

    protected $casts = [
        'reminded_at' => 'datetime',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * A permanent link the customer can open without an account.
     */
    public function publicUrl(): string
    {
        return URL::signedRoute('public.estimates.show', ['estimate' => $this->id]);
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
        return $this->hasMany(EstimateItem::class)->orderBy('position');
    }

    /**
     * Turn the estimate into a draft invoice with the same lines, once. A second
     * call returns the invoice made the first time.
     */
    public function convertToInvoice(): Invoice
    {
        // Locking the row makes a double click wait for the first conversion and
        // then return its invoice instead of making a second one.
        return DB::transaction(function () {
            $estimate = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($estimate->invoice) {
                return $estimate->invoice;
            }

            $invoice = Invoice::create([
                'customer_id' => $estimate->customer_id,
                'status' => 'draft',
                'issue_date' => today(),
                'due_date' => today()->addDays(30),
                'currency' => $estimate->currency,
                'notes' => $estimate->notes,
                'terms' => $estimate->terms,
            ]);
            $invoice->syncItems($estimate->itemRows());

            $estimate->invoice()->associate($invoice);
            $estimate->status = 'accepted';
            $estimate->save();

            $this->setRawAttributes($estimate->getAttributes(), true);
            $this->setRelation('invoice', $invoice);

            return $invoice;
        });
    }

    public static function statuses(): array
    {
        return [
            'draft' => __('draft'),
            'sent' => __('sent'),
            'accepted' => __('accepted'),
            'declined' => __('declined'),
        ];
    }

    public static function statusColors(): array
    {
        return ['draft' => 'secondary', 'sent' => 'info', 'accepted' => 'success', 'declined' => 'danger'];
    }
}
