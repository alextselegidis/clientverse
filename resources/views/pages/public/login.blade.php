{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Open Source CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */
--}}

@extends('layouts.public_layout')

@section('page_title')
    {{__('login')}}
@endsection

@section('content')
    <h4 class="text-center text-muted fw-lighter">
        {{__('welcomeBackToMessage')}}
    </h4>

    <h2 class="text-center fw-bolder">
        {{config('app.name')}}
    </h2>

    <form action="{{route('public.login.perform')}}" method="POST">
        @csrf
        @method('POST')

        <div class="mb-3">
            <label for="email" class="form-label">
                {{__('email')}} *
            </label>
            <input type="email" name="email" class="form-control" maxlength="100" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                {{__('password')}} *
            </label>
            <input type="password" name="password" class="form-control" maxlength="100" required>
        </div>

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                {{__('remember')}}
            </label>
        </div>

        <div class="d-flex flex-column flex-lg-row-reverse justify-content-lg-between">
            <button type="submit" class="btn btn-primary mb-3 mb-lg-0">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                {{__('logIn')}}
            </button>

            <a href="{{route('public.recovery.index')}}" class="btn btn-link">
                {{__('forgotPasswordPrompt')}}
            </a>
        </div>
    </form>
@endsection

