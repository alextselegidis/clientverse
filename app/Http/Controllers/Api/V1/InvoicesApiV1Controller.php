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

use App\Models\Invoice;
use Orion\Http\Controllers\Controller;

/**
 * Read only: totals and balances are worked out by the app, so these are written on the web pages.
 */
class InvoicesApiV1Controller extends Controller
{
    protected $model = Invoice::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['number', 'status', 'customer_id', 'currency', 'issue_date', 'due_date', 'total', 'amount_paid', 'recurring_interval', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['number', 'issue_date', 'due_date', 'total', 'amount_paid', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['number', 'notes'];
    }

    public function includes(): array
    {
        return ['customer', 'items', 'payments', 'creditNotes', 'subscription'];
    }
}
