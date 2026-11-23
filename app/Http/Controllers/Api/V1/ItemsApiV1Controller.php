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

use App\Http\Requests\Api\ItemRequest;
use App\Models\Item;

class ItemsApiV1Controller extends ValidatedApiV1Controller
{
    protected $model = Item::class;

    protected $request = ItemRequest::class;

    public function filterableBy(): array
    {
        return ['description', 'unit_price', 'tax_rate', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['description', 'unit_price', 'tax_rate', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['description'];
    }
}
