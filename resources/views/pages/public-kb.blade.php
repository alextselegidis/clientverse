{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 860px;">
    <h1 class="h3 mb-1">{{ __('knowledge_base') }}</h1>
    <p class="text-muted mb-4">{{ setting('company_name') ?: config('app.name') }}</p>

    <form action="{{ route('kb') }}" method="GET" class="mb-4">
        <div class="input-group">
            <span class="input-group-text border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="search" name="q" class="form-control border-start-0" value="{{ $q }}"
                   placeholder="{{ __('search') }}..." aria-label="{{ __('search') }}">
        </div>
    </form>

    @forelse($groups as $category => $articles)
        <section class="mb-4">
            <h2 class="h6 text-uppercase text-muted">{{ $category }}</h2>
            <div class="list-group">
                @foreach($articles as $article)
                    <a href="{{ route('kb.show', $article->slug) }}" class="list-group-item list-group-item-action">{{ $article->title }}</a>
                @endforeach
            </div>
        </section>
    @empty
        <p class="text-muted">{{ __('no_records_found') }}</p>
    @endforelse
</div>
</body>
</html>
