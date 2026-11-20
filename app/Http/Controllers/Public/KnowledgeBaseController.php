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

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * The public knowledge base: public articles only, no sign-in.
 */
class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $articles = Article::where('is_public', true)
            ->when($q, fn ($query) => $query->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%")))
            ->orderBy('category')
            ->orderBy('title')
            ->get()
            ->groupBy(fn (Article $article) => $article->category ?: __('general'));

        return view('pages.public-kb', ['groups' => $articles, 'q' => $q]);
    }

    public function show(string $slug)
    {
        return view('pages.public-kb-article', [
            'article' => Article::where('is_public', true)->where('slug', $slug)->firstOrFail(),
        ]);
    }
}
