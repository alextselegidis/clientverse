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

use App\Models\Concerns\HasCode;
use App\Models\Concerns\HasCustomFields;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasCode;
    use HasCustomFields;
    use RecordsActivity;
    use SoftDeletes;

    public const CODE_PREFIX = 'S';

    protected $fillable = [
        'customer_id',
        'name',
        'value',
        'currency',
        'stage',
        'probability',
        'expected_close_date',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Stamp the moment a deal is won and clear it if it is reopened; dashboards count sales by it.
        static::saving(function (Sale $sale) {
            if ($sale->isDirty('stage') || !$sale->exists) {
                $sale->won_at = $sale->stage === 'won' ? ($sale->getOriginal('stage') === 'won' ? $sale->won_at : now()) : null;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getExpectedValueAttribute()
    {
        return $this->value * ($this->probability / 100);
    }

    /**
     * Sum the deal values of the current query, one total per currency.
     *
     * Every sale carries its own currency, so a single SUM() over mixed
     * currencies is a meaningless number. Returns currency => total, largest
     * first.
     */
    public function scopeTotalsByCurrency($query)
    {
        return $query
            ->selectRaw('currency, SUM(value) AS total')
            ->groupBy('currency')
            ->orderByDesc('total')
            ->pluck('total', 'currency')
            ->map(fn ($total) => (float) $total);
    }

    public static function stages(): array
    {
        return [
            'lead' => __('lead'),
            'qualified' => __('qualified'),
            'proposal_sent' => __('proposal_sent'),
            'won' => __('won'),
            'lost' => __('lost'),
        ];
    }

    public static function toOptions($where = null)
    {
        $query = self::query();

        if ($where) {
            $query->where($where);
        }

        return $query->selectRaw('name AS label, id AS value')->get();
    }
}
