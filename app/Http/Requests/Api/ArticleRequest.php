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

class ArticleRequest extends ApiRequest
{
    protected function fields(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'body' => 'required|string',
            'is_public' => 'boolean',
        ];
    }
}
