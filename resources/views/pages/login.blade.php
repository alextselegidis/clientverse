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

@extends('layouts.auth-layout')

@section('pageTitle')
    {{__('login')}}
@endsection

@section('content')
    <h1 class="display-6 text-center mb-3">
        {{__('sign_in')}}
    </h1>

    <p class="fs-5 text-muted text-center mb-5">
        {{__('enter_details_message')}}
    </p>

    @include('shared.errors', ['class' => 'mx-2 mx-lg-5 text-center'])

    <form action="{{route('login.perform')}}" method="POST" class="mx-5 my-3 my-lg-5 px-lg-5">
        @csrf
        @method('POST')

        <div class="mb-3">
            <label for="email" class="form-label">
                {{__('email')}} <span class="text-danger">*</span>
            </label>
            <input type="email" name="email" class="form-control" maxlength="100" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                {{__('password')}} <span class="text-danger">*</span>
            </label>
            <input type="password" name="password" class="form-control" maxlength="100" required>
        </div>

        {{-- <div class="d-flex flex-column align-items-center flex-lg-row-reverse justify-content-lg-between mb-3"> --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    {{__('remember')}}
                </label>
            </div>

            <a href="{{route('recovery')}}" class="btn btn-link px-0">
                {{__('forgot_password')}}
            </a>
        </div>

        <button type="submit" class="btn btn-primary mb-3 mb-lg-0 w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            {{__('log_in')}}
        </button>

    </form>

@endsection

