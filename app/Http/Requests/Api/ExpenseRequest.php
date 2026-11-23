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

namespace App\Http\Requests\Api;

class ExpenseRequest extends ApiRequest
{
    protected function fields(): array
    {
        return [
            'expense_date' => 'required|date',
            'category' => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'currency' => 'required|string|size:3',
            'billable' => 'boolean',
            'customer_id' => 'nullable|required_if_accepted:billable|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string',
        ];
    }
}
