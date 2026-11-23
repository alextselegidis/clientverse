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

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\ExpenseRequest;
use App\Models\Expense;

class ExpensesApiV1Controller extends ValidatedApiV1Controller
{
    protected $model = Expense::class;

    protected $request = ExpenseRequest::class;

    public function filterableBy(): array
    {
        return ['expense_date', 'category', 'amount', 'currency', 'billable', 'customer_id', 'project_id', 'invoice_id', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['expense_date', 'category', 'amount', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['description', 'category', 'notes'];
    }

    public function includes(): array
    {
        return ['customer', 'project', 'invoice'];
    }
}
