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

use Orion\Http\Requests\Request;

/**
 * Validation for an API resource that writes. fields() is what a create needs; an
 * update checks the same rules but only for the fields it sends.
 */
abstract class ApiRequest extends Request
{
    abstract protected function fields(): array;

    public function storeRules(): array
    {
        return $this->fields();
    }

    public function updateRules(): array
    {
        return array_map(
            fn ($rule) => is_string($rule) ? 'sometimes|' . $rule : array_merge(['sometimes'], $rule),
            $this->fields(),
        );
    }
}
