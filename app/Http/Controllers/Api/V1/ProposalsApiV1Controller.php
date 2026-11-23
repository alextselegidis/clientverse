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

use App\Models\Proposal;
use Orion\Http\Controllers\Controller;

/**
 * Read only: totals and balances are worked out by the app, so these are written on the web pages.
 */
class ProposalsApiV1Controller extends Controller
{
    protected $model = Proposal::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['number', 'subject', 'status', 'customer_id', 'sale_id', 'currency', 'issue_date', 'open_till', 'total', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['number', 'subject', 'issue_date', 'open_till', 'total', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['number', 'subject'];
    }

    public function includes(): array
    {
        return ['customer', 'sale', 'estimate', 'items'];
    }
}
