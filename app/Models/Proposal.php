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

class Proposal extends Model
{
    use HasDocumentNumber;
    use HasLineItems;
    use RecordsActivity;
    use SoftDeletes;

    public const NUMBER_PREFIX = 'PRO';

    protected static function booted(): void
    {
        // A new open_till earns a new reminder.
        // Written straight to the row: a model loaded before the reminder went out
        // still holds reminded_at = null, so setting it on the model would save nothing.
        static::updated(function (self $model) {
            if ($model->wasChanged('open_till')) {
                $model->newQuery()->whereKey($model->getKey())->toBase()->update(['reminded_at' => null]);
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'sale_id',
        'number',
        'subject',
        'content',
        'status',
        'issue_date',
        'open_till',
        'currency',
        'notes',
        'terms',
    ];

    protected $casts = [
        'reminded_at' => 'datetime',
        'issue_date' => 'date',
        'open_till' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * A permanent link the customer can open without an account.
     */
    public function publicUrl(): string
    {
        return URL::signedRoute('public.proposals.show', ['proposal' => $this->id]);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }

    public function items()
    {
        return $this->hasMany(ProposalItem::class)->orderBy('position');
    }

    /**
     * Turn the proposal into a draft estimate with the same lines, once. Locked
     * like Estimate::convertToInvoice() so a double click makes one estimate.
     */
    public function convertToEstimate(): Estimate
    {
        return DB::transaction(function () {
            $proposal = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($proposal->estimate) {
                return $proposal->estimate;
            }

            $estimate = Estimate::create([
                'customer_id' => $proposal->customer_id,
                'status' => 'draft',
                'issue_date' => today(),
                'expiry_date' => today()->addDays(30),
                'currency' => $proposal->currency,
                'notes' => $proposal->notes,
                'terms' => $proposal->terms,
            ]);
            $estimate->syncItems($proposal->itemRows());

            $proposal->estimate()->associate($estimate);
            $proposal->save();

            $this->setRawAttributes($proposal->getAttributes(), true);
            $this->setRelation('estimate', $estimate);

            return $estimate;
        });
    }

    // Same lifecycle as an estimate.
    public static function statuses(): array
    {
        return Estimate::statuses();
    }

    public static function statusColors(): array
    {
        return Estimate::statusColors();
    }
}
