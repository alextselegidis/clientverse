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

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Shared by invoices and estimates: an ordered list of priced, taxed lines and a
 * cached `total` column. The using model defines the items() relation.
 */
trait HasLineItems
{
    /**
     * The largest amount a decimal(12,2) column holds.
     */
    public const MAX_AMOUNT = 9999999999.99;

    public function getSubtotalAttribute(): float
    {
        return round($this->items->sum(fn ($item) => $item->subtotal), 2);
    }

    public function getTaxAttribute(): float
    {
        return round($this->items->sum(fn ($item) => $item->tax), 2);
    }

    /**
     * Replace every line with the given rows. Rows without a description are
     * dropped, which is how a line is removed from the form.
     */
    public function syncItems(array $rows): void
    {
        $rows = array_values(array_filter($rows, fn ($row) => filled($row['description'] ?? null)));

        // The total column holds 12 digits; refuse rather than let MySQL throw halfway.
        $total = collect($rows)->sum(fn ($row) => (float) ($row['quantity'] ?? 1) * (float) ($row['unit_price'] ?? 0)
            * (1 + (float) ($row['tax_rate'] ?? 0) / 100));

        if (abs($total) > self::MAX_AMOUNT) {
            throw ValidationException::withMessages(['items' => __('amount_too_large_message')]);
        }

        // All lines or none: a failure part way must not leave the document half empty.
        DB::transaction(function () use ($rows) {
            $this->items()->delete();

            foreach ($rows as $position => $row) {
                $this->items()->create([
                    'description' => $row['description'],
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_price' => $row['unit_price'] ?? 0,
                    'tax_rate' => $row['tax_rate'] ?? 0,
                    'position' => $position,
                ]);
            }

            $this->recalculate();
        });
    }

    /**
     * The lines as plain arrays, ready for another document's syncItems().
     */
    public function itemRows(): array
    {
        return $this->items->map->only(['description', 'quantity', 'unit_price', 'tax_rate'])->all();
    }

    public function recalculate(): void
    {
        $this->load('items');

        $this->total = round($this->subtotal + $this->tax, 2);
        $this->save();
    }
}
