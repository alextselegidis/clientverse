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
    {{ $article->exists ? $article->title : __('create') . ' ' . __('article') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('knowledge_base'), 'url' => route('articles')],
        // An existing record links back to its page, so the trail leads out of the form.
        ...($article->exists
            ? [['label' => $article->title, 'url' => route('articles.show', $article->id)], ['label' => __('edit')]]
            : [['label' => __('create')]]),
    ]])
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $article->exists ? route('articles.update', $article->id) : route('articles.store') }}" method="POST" id="edit-form">
                        @csrf
                        @if($article->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">{{ __('title') }} <span class="text-danger">*</span></label>
                                <input type="text" id="title" name="title" class="form-control" required autofocus maxlength="255"
                                       value="{{ old('title', $article->title) }}">
                                @error('title')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">{{ __('category') }}</label>
                                <input type="text" id="category" name="category" class="form-control" maxlength="100" list="article-categories"
                                       value="{{ old('category', $article->category) }}">
                                <datalist id="article-categories">
                                    @foreach($categories as $option)
                                        <option value="{{ $option }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">{{ __('content') }} <span class="text-danger">*</span></label>
                            <textarea id="body" name="body" class="form-control font-monospace" rows="16" required>{{ old('body', $article->body) }}</textarea>
                            <div class="form-text">{{ __('markdown_hint') }}</div>
                            @error('body')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox" id="is_public" name="is_public" value="1" class="form-check-input"
                                   @checked(old('is_public', $article->is_public))>
                            <label for="is_public" class="form-check-label">{{ __('publish_in_public_kb') }}</label>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
