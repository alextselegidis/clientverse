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

@extends('layouts.auth-layout')

@section('pageTitle')
    {{ __('install') }}
@endsection

@section('content')
    @php
        // [name, label, input type, default, autocomplete]
        $sections = [
            'install_site' => [
                ['app_url', 'install_app_url', 'url', $appUrl, 'url'],
            ],
            'install_database' => [
                ['db_host', 'install_db_host', 'text', 'localhost', 'off'],
                ['db_port', 'install_db_port', 'number', '3306', 'off'],
                ['db_database', 'install_db_database', 'text', 'clientverse', 'off'],
                ['db_username', 'install_db_username', 'text', '', 'off'],
                ['db_password', 'install_db_password', 'password', '', 'off'],
            ],
            'install_admin' => [
                ['name', 'name', 'text', '', 'name'],
                ['email', 'email', 'email', '', 'email'],
                ['password', 'password', 'password', '', 'new-password'],
                ['password_confirmation', 'password_confirmation', 'password', '', 'new-password'],
            ],
        ];
        $ready = !in_array(false, $requirements, true);
    @endphp

    <h1 class="display-6 text-center mb-3">{{ __('install') }}</h1>

    <p class="text-muted text-center mb-4 mx-4">{{ __('install_message') }}</p>

    @include('shared.errors', ['class' => 'mx-3 mx-lg-5'])

    <div class="mx-3 mx-lg-5 mb-4">
        <h2 class="h6 mb-2">{{ __('install_requirements') }}</h2>
        <ul class="list-unstyled small mb-0">
            @foreach($requirements as $label => $met)
                <li class="py-1">
                    <i class="bi bi-{{ $met ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }} me-2"></i>{{ $label }}
                </li>
            @endforeach
        </ul>
    </div>

    <form action="{{ route('install.store') }}" method="POST" class="mx-3 mx-lg-5 mb-3">
        @csrf

        @foreach($sections as $section => $fields)
            <h2 class="h6 mt-4 mb-3">{{ __($section) }}</h2>

            @foreach($fields as [$name, $label, $type, $default, $autocomplete])
                <div class="mb-3">
                    <label for="{{ $name }}" class="form-label">{{ __($label) }}</label>
                    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" class="form-control"
                           autocomplete="{{ $autocomplete }}" @required($name !== 'db_password')
                           @if($type !== 'password') value="{{ old($name, $default) }}" @endif>
                </div>
            @endforeach
        @endforeach

        <button type="submit" class="btn btn-primary w-100 mt-2" @disabled(!$ready)>
            <i class="bi bi-download me-2"></i>{{ __('install') }}
        </button>
    </form>
@endsection
