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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')

    @yield('styles')
</head>
<body class="main-layout">
<div class="app-shell">

    @include('shared.sidebar')

    <div class="app-main">

    @include('shared.header')

    <!-- Page Heading -->

    @hasSection('pageTitle')
        <div class="app-page-header">
            <div class="flex-grow-1">
                @if(View::hasSection('breadcrumbs'))
                    @yield('breadcrumbs')
                @endif
                @hasSection('recordCode')
                    {{-- The record's shareable code (C0042); pasting it into search opens the record. --}}
                    <div class="app-record-code">@yield('recordCode')</div>
                @endif
                <h1 class="app-page-title">@yield('pageTitle')</h1>
            </div>
            @hasSection('navActions')
                <nav class="page-actions" aria-label="{{ __('actions') }}">
                    @yield('navActions')
                </nav>
            @endif
        </div>
    @endif

    <!-- Page Content -->
    <main class="app-content">
        @yield('content')
    </main>

    <!-- Toast Container (Bottom Right using Bootstrap classes) -->
    <div class="toast-container position-fixed bottom-0 end-0 mb-5 p-3">

        <!-- Success Toast -->
        @if (session('success'))
            <div class="toast align-items-center text-bg-success border-0 show mb-2" role="alert" aria-live="assertive"
                 aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Error Toast -->
        @if (session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive"
                 aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                </div>
            </div>
        @endif

    </div>

    <footer class="app-footer">
        @include('shared.footer')
    </footer>

    </div>
</div>

<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
{{-- TEMP: pace off <script src="vendor/pace-js/pace.min.js"></script> --}}
<script src="scripts/clientverse.js?{{config('app.version')}}"></script>
@include('shared.service-worker')

@yield('scripts')
</body>
</html>
