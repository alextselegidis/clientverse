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

use App\Http\Requests\Api\ArticleRequest;
use App\Models\Article;

class ArticlesApiV1Controller extends ValidatedApiV1Controller
{
    protected $model = Article::class;

    protected $request = ArticleRequest::class;

    public function filterableBy(): array
    {
        return ['title', 'category', 'is_public', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['title', 'category', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['title', 'category', 'body'];
    }
}
