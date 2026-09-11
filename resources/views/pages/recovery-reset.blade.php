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
    {{__('recovery')}}
@endsection

@section('content')
    <h2 class="text-center fw-bolder mb-4">
        {{__('reset_password_message')}}
    </h2>

    <p class="text-center text-muted mx-5 mb-3 mb-lg-5">
        {{__('enter_new_password_message')}}
    </p>

    @include('shared.errors', ['class' => 'mx-2 mx-lg-5 text-center'])

    <form action="{{route('password.update')}}" method="POST" class="mx-5 my-3 my-lg-5 px-lg-5">
        @csrf
        @method('POST')

        <input type="hidden" name="token" value="{{$token}}">

        <div class="mb-3">
            <label for="email" class="form-label">
                {{__('email')}} <span class="text-danger">*</span>
            </label>
            <input type="email" id="email" name="email" class="form-control" maxlength="100"
                   autocomplete="email" value="{{old('email', $email)}}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">
                {{__('new_password')}} <span class="text-danger">*</span>
            </label>
            <input type="password" id="password" name="password" class="form-control" minlength="8"
                   autocomplete="new-password" autofocus required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                {{__('confirm_password')}} <span class="text-danger">*</span>
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                   minlength="8" autocomplete="new-password" required>
        </div>

        <div class="d-flex flex-column flex-lg-row-reverse justify-content-lg-between gap-2">
            <button type="submit" class="btn btn-primary mb-2 mb-lg-0 flex-grow-1 w-100">
                {{__('save')}}
            </button>

            <a href="{{route('login')}}" class="btn btn-outline-primary flex-grow-1 w-100">
                {{__('back')}}
            </a>
        </div>
    </form>

@endsection
