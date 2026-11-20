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
    <a href="{{ route('kb') }}" class="d-inline-block mb-3"><i class="bi bi-arrow-left me-1"></i>{{ __('knowledge_base') }}</a>
    <article class="bg-white shadow-sm rounded-3 p-4 p-md-5 kb-article">
        <h1 class="h3 mb-3">{{ $article->title }}</h1>
        {{ $article->body_html }}
        <p class="small text-muted mt-4 mb-0">{{ __('updated') }}: {{ $article->updated_at->format('M d, Y') }}</p>
    </article>
</div>
</body>
</html>
