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
    {{ $announcement->exists ? $announcement->title : __('create') . ' ' . __('announcement') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('announcements'), 'url' => route('setup.announcements')],
        ['label' => $announcement->exists ? $announcement->title : __('create')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ $announcement->exists ? route('setup.announcements.update', $announcement->id) : route('setup.announcements.store') }}"
                          method="POST" id="edit-form">
                        @csrf
                        @if($announcement->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('title') }} <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('title', $announcement->title) }}">
                            @error('title')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">{{ __('message') }} <span class="text-danger">*</span></label>
                            <textarea id="body" name="body" class="form-control" rows="8" required>{{ old('body', $announcement->body) }}</textarea>
                            <div class="form-text">{{ __('markdown_hint') }}</div>
                            @error('body')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="audience" class="form-label">{{ __('audience') }}</label>
                                <select id="audience" name="audience" class="form-select">
                                    @foreach(\App\Models\Announcement::audiences() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('audience', $announcement->audience) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('audience')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="visible_until" class="form-label">{{ __('visible_until') }}</label>
                                <input type="date" id="visible_until" name="visible_until" class="form-control"
                                       value="{{ old('visible_until', $announcement->visible_until?->toDateString()) }}">
                                <div class="form-text">{{ __('visible_until_hint') }}</div>
                                @error('visible_until')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
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
