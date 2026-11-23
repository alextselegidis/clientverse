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
{{-- Client portal sign-in for customer contacts; see PortalLoginController. --}}
@extends('layouts.auth-layout')

@section('pageTitle')
    {{ __('client_portal') }}
@endsection

@section('content')
    <h1 class="display-6 text-center mb-3">{{ __('client_portal') }}</h1>

    <p class="fs-5 text-muted text-center mb-5">{{ setting('company_name') ?: config('app.name') }}</p>

    @include('shared.errors', ['class' => 'mx-2 mx-lg-5 text-center'])

    <form action="{{ route('portal.login.perform') }}" method="POST" class="mx-5 my-3 my-lg-5 px-lg-5">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('email') }} <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100"
                   value="{{ old('email') }}" autocomplete="email" autofocus required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('password') }} <span class="text-danger">*</span></label>
            <input type="password" id="password" name="password" class="form-control" maxlength="100"
                   autocomplete="current-password" required>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="remember" name="remember">
            <label class="form-check-label" for="remember">{{ __('remember') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('log_in') }}
        </button>
    </form>
@endsection
