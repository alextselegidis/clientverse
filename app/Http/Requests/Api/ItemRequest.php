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

class ItemRequest extends ApiRequest
{
    protected function fields(): array
    {
        return [
            'description' => 'required|string|max:255',
            'unit_price' => 'required|numeric|between:-9999999999.99,9999999999.99',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ];
    }
}
