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
@extends('layouts.main-layout')

@section('pageTitle')
    {{ $article->title }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('knowledge_base'), 'url' => route('articles')],
        ['label' => $article->title]
    ]])
@endsection

@section('navActions')
    @if($article->is_public)
        <a href="{{ route('kb.show', $article->slug) }}" class="nav-link me-lg-3" target="_blank">
            <i class="bi bi-box-arrow-up-right me-2"></i>
            {{ __('public') }}
        </a>
    @endif
    <a href="{{ route('articles.edit', $article->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('articles.destroy', $article->id) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <article class="card border-0 shadow-sm">
                <div class="card-body p-4 kb-article">
                    <h1 class="h3 mb-3">{{ $article->title }}</h1>
                    {{ $article->body_html }}
                </div>
            </article>
        </div>
    </div>
@endsection
