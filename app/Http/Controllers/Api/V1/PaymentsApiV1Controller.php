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

use App\Models\Payment;
use Orion\Http\Controllers\Controller;

/**
 * Read only: totals and balances are worked out by the app, so these are written on the web pages.
 */
class PaymentsApiV1Controller extends Controller
{
    protected $model = Payment::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['invoice_id', 'paid_on', 'amount', 'method', 'reference', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['paid_on', 'amount', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['reference', 'method'];
    }

    public function includes(): array
    {
        return ['invoice'];
    }
}
