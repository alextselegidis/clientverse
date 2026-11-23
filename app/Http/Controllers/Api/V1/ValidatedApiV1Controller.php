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

use Orion\Http\Controllers\Controller;
use Orion\Http\Requests\Request;

/**
 * A resource that writes only the fields its request class validated, so an
 * unchecked fillable column (a ticket's status on create, say) cannot slip in.
 */
abstract class ValidatedApiV1Controller extends Controller
{
    protected $authorizationDisabled = true;

    protected function retrieve(Request $request, ?string $key = null, $default = null)
    {
        return $key ? data_get($request->validated(), $key, $default) : $request->validated();
    }
}
