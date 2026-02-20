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

use App\Models\CustomerNote;
use Orion\Http\Controllers\Controller;

class CustomerNotesApiV1Controller extends Controller
{
    protected $model = CustomerNote::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['customer_id', 'user_id', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['content'];
    }

    public function includes(): array
    {
        return ['customer', 'user'];
    }
}
