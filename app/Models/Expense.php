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
        if ($this->invoice) {
            return $this->invoice;
        }

        $invoice = Invoice::create([
            'customer_id' => $this->customer_id,
            'status' => 'draft',
            'issue_date' => today(),
            'due_date' => today()->addDays(30),
            'currency' => $this->currency,
        ]);
        $invoice->syncItems([[
            'description' => $this->description,
            'quantity' => 1,
            'unit_price' => $this->amount,
            'tax_rate' => 0,
        ]]);

        $this->invoice()->associate($invoice)->save();

        return $invoice;
    }

    /**
     * Categories already in use, for the input's suggestions.
     */
    public static function categories(): array
    {
        return self::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}
