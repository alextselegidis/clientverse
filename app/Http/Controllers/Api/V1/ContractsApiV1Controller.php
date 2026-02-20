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

use App\Models\Contract;
use Orion\Http\Controllers\Controller;

class ContractsApiV1Controller extends Controller
{
    protected $model = Contract::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['title', 'type', 'status', 'value', 'currency', 'customer_id', 'project_id', 'sale_id', 'start_date', 'end_date', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['title', 'type', 'status', 'value', 'start_date', 'end_date', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['title', 'description', 'notes'];
    }

    public function includes(): array
    {
        return ['customer', 'project', 'sale'];
    }
}
