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
    {{__('About')}}
@endsection
@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('about')]
    ]])
@endsection
@section('content')
    <div>
        <div class="mx-auto my-5 text-center" style="max-width: 600px">
            <div class="mb-5">
                <img src="images/logo.png" alt="Logo" class="me-2" style="height: 128px">
            </div>
            <h1 class="fs-3 mb-5">
                Clientverse <span class="text-muted">v{{config('app.version')}}</span>
            </h1>
            <div class="mb-5 text-secondary">
                Clientverse is an open-source bookmark management application that helps you organize, categorize, and
                access your favorite web links in one streamlined place. Save, tag, and search your bookmarks with
                a clean and intuitive interface, keeping your digital collections tidy and easily retrievable.
            </div>
            <div class="d-flex gap-2 justify-content-center">
                <a href="https://github.com/alextselegidis/clientverse" class="btn btn-outline-primary btn-equal-width" target="_blank" style="min-width: 180px;">
                    <i class="bi bi-github me-2"></i>
                    GitHub
                </a>
                <a href="https://alextselegidis.com" class="btn btn-outline-secondary btn-equal-width" target="_blank" style="min-width: 180px;">
                    <img src="images/alextselegidis-logo-16x16.png" alt="logo" class="me-2"/>
                    alextselegidis.com
                </a>
            </div>

            <hr class="my-5">

            <div class="mb-4">
                <h2 class="fs-4 mb-3">
                    <i class="bi bi-star-fill text-warning me-2"></i>{{ __('premium') }}
                </h2>
                <p class="text-secondary mb-4">
                    {{ __('premium_description') }}
                </p>
                <a href="https://clientverse.org/premium" class="btn btn-primary btn-lg w-100" target="_blank">
                    <i class="bi bi-star me-2"></i>{{ __('get_premium') }}
                </a>
            </div>
        </div>
    </div>
@endsection
