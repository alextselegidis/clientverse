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

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        $q = $request->query('q');

        if ($q) {
            $query->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%"));
        }

        sort_query($query, $request->query('sort', 'title'), $request->query('direction', 'asc'));

        return view('pages.articles', [
            'articles' => $query->paginate(25)->withQueryString(),
            'q' => $q,
        ]);
    }

    public function create()
    {
        return view('pages.articles-edit', ['article' => new Article(), 'categories' => Article::categories()]);
    }

    public function store(Request $request)
    {
        $article = Article::create($this->validateArticle($request));

        return redirect(route('articles.show', $article->id))->with('success', __('record_saved_message'));
    }

    public function show(Article $article)
    {
        return view('pages.articles-show', ['article' => $article]);
    }

    public function edit(Article $article)
    {
        return view('pages.articles-edit', ['article' => $article, 'categories' => Article::categories()]);
    }

    public function update(Request $request, Article $article)
    {
        $article->update($this->validateArticle($request));

        return redirect(route('articles.show', $article->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect(route('articles'))->with('success', __('record_deleted_message'));
    }

    private function validateArticle(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'body' => 'required|string',
            'is_public' => 'boolean',
        ]);
    }
}
