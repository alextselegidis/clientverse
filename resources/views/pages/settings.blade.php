{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
--}}

@extends('layouts.main-layout')

@section('pageTitle')
    {{ __('localization') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup')],
        ['label' => __('localization')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Sidebar -->
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">


            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('setup.localization.update') }}" method="POST" id="settings-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="default-locale" class="form-label text-dark small fw-medium">
                                <span class="text-danger">*</span> {{ __('default_locale') }}
                            </label>
                            @include('shared.locale-dropdown', [
                                'name' => 'default_locale',
                                'id' => 'default-locale',
                                'value' => $defaultLocale,
                                'required'=> true,
                            ])
                            @error('default_locale')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default-timezone" class="form-label text-dark small fw-medium">
                                <span class="text-danger">*</span> {{ __('default_timezone') }}
                            </label>

                            @include('shared.timezone-dropdown', [
                                'name' => 'default_timezone',
                                'id' => 'default-timezone',
                                'value' => $defaultTimezone,
                                'required'=> true,
                            ])

                            @error('default_timezone')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>

                <!-- Card Footer with Save Button -->
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="settings-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
