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
    {{__('recovery')}}
@endsection

@section('content')
    <h2 class="text-center fw-bolder mb-4">
        {{__('resetPasswordMessage')}}
    </h2>

    <p class="text-center text-muted mx-5 mb-3 mb-lg-5">
        {{__('enterEmailMessage')}}
    </p>

    @include('shared.errors', ['class' => 'mx-2 mx-lg-5 text-center'])

    <form action="{{route('recovery.perform')}}" method="POST" class="mx-5 my-3 my-lg-5 px-lg-5">
        @csrf
        @method('POST')

        <div class="mb-3">
            <label for="email" class="form-label">
                {{__('email')}}
            </label>
            <input type="email" name="email" class="form-control" maxlength="100" required>
        </div>

        <div class="d-flex flex-column flex-lg-row-reverse justify-content-lg-between gap-2">
            <button type="submit" class="btn btn-primary mb-2 mb-lg-0 flex-grow-1 w-100">
                {{__('continue')}}
            </button>

            <a href="{{route('login')}}" class="btn btn-outline-primary flex-grow-1 w-100">
                {{__('back')}}
            </a>
        </div>
    </form>

@endsection

