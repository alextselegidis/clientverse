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

@extends('layouts.message-layout')

@section('pageTitle')
    {{__('welcome')}}
@endsection

@section('content')
    <div class="text-center">
        <h1 class="display-6 mb-3">
            Welcome To
        </h1>

        <p class="fs-5 text-muted mb-5">
            Simple Bookmark Manager
        </p>

        <a href="{{route('login')}}" class="btn btn-primary" style="min-width: 12rem">
            {{__('continue')}}
        </a>
    </div>
@endsection

