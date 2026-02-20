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

use App\Models\Contact;
use Orion\Http\Controllers\Controller;

class ContactsApiV1Controller extends Controller
{
    protected $model = Contact::class;

    protected $authorizationDisabled = true;

    public function filterableBy(): array
    {
        return ['first_name', 'last_name', 'email', 'phone', 'role', 'is_primary', 'customer_id', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['first_name', 'last_name', 'email', 'role', 'is_primary', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['first_name', 'last_name', 'email', 'phone', 'position'];
    }

    public function includes(): array
    {
        return ['customer'];
    }
}
