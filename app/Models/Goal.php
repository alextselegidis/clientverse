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

class Goal extends Model
{
    use RecordsActivity;

    protected $fillable = ['name', 'metric', 'currency', 'target', 'start_date', 'end_date'];

    protected $casts = [
        'target' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    private ?float $progressCache = null;

    /**
     * How far the goal has got, measured live from the records in its period.
     */
    public function getProgressAttribute(): float
    {
        // The goals page reads this several times per card; count once.
        return $this->progressCache ??= $this->measureProgress();
    }

    private function measureProgress(): float
    {
        $range = [$this->start_date->toDateString(), $this->end_date->toDateString()];
        $until = $this->end_date->copy()->endOfDay();

        return (float) match ($this->metric) {
            'invoiced' => Invoice::where('status', 'sent')->where('currency', $this->currency)
                ->whereBetween('issue_date', $range)->sum('total'),
            'collected' => Payment::whereBetween('paid_on', $range)
                ->whereHas('invoice', fn ($query) => $query->where('currency', $this->currency))->sum('amount'),
            'new_customers' => Customer::whereBetween('created_at', [$this->start_date, $until])->count(),
            'won_deals' => Sale::where('stage', 'won')->whereBetween('won_at', [$this->start_date, $until])->count(),
        };
    }

    public function getPercentAttribute(): int
    {
        return (float) $this->target > 0 ? (int) min(100, round($this->progress / (float) $this->target * 100)) : 0;
    }

    public function isMoney(): bool
    {
        return in_array($this->metric, ['invoiced', 'collected'], true);
    }

    public function format(float $value): string
    {
        return $this->isMoney() ? format_currency($value, $this->currency) : (string) (int) $value;
    }

    public static function metrics(): array
    {
        return [
            'invoiced' => __('amount_invoiced'),
            'collected' => __('amount_collected'),
            'new_customers' => __('new_customers'),
            'won_deals' => __('won_deals'),
        ];
    }
}
