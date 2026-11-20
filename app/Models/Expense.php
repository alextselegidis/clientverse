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
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'project_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'currency',
        'billable',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'billable' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Put the expense on a new draft invoice for its customer, once.
     */
    public function bill(): Invoice
    {
        // Locked, so a double click cannot bill the same expense twice.
        return DB::transaction(function () {
            $expense = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($expense->invoice) {
                return $expense->invoice;
            }

            $invoice = Invoice::create([
                'customer_id' => $expense->customer_id,
                'status' => 'draft',
                'issue_date' => today(),
                'due_date' => today()->addDays(30),
                'currency' => $expense->currency,
            ]);
            $invoice->syncItems([[
                'description' => $expense->description,
                'quantity' => 1,
                'unit_price' => $expense->amount,
                'tax_rate' => 0,
            ]]);

            $expense->invoice()->associate($invoice)->save();
            $this->setRawAttributes($expense->getAttributes(), true);
            $this->setRelation('invoice', $invoice);

            return $invoice;
        });
    }

    /**
     * Categories already in use, for the input's suggestions.
     */
    public static function categories(): array
    {
        return self::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}
