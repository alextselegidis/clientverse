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

use App\Models\Customer;
use Orion\Http\Controllers\Controller;

class CustomersApiV1Controller extends Controller
{
    protected $model = Customer::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['name', 'email', 'phone', 'company', 'type', 'status', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['name', 'email', 'company', 'type', 'status', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['name', 'email', 'phone', 'company', 'address'];
    }

    public function includes(): array
    {
        return ['contacts', 'projects', 'contracts', 'sales', 'tags', 'customerNotes'];
    }
}
