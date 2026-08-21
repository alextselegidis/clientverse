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
<body class="main-layout d-flex flex-column min-vh-100 bg-light">
<div class="flex-grow-1">

    @include('shared.header')

    <!-- Page Heading -->

    @hasSection('pageTitle')
        <header class="bg-body-secondary mb-3">
            <div class="container">
                <div class="row">

                    <nav class="navbar navbar-expand-lg">
                        <div class="container-fluid">
                            <div class="d-flex flex-column w-100 w-lg-auto py-2 py-lg-3 overflow-hidden">
                                @if(View::hasSection('breadcrumbs'))
                                    @yield('breadcrumbs')
                                @endif
                            </div>
                            @hasSection('navActions')
                                <nav class="navbar-nav flex-row flex-wrap justify-content-end ms-auto column-gap-3">
                                    @yield('navActions')
                                </nav>
                            @endif
                        </div>
                    </nav>

                </div>
            </div>
        </header>
    @endif

    <!-- Page Content -->
    <main class="container mb-4">
        <div class="row">
            <div class="col">
                @yield('content')
            </div>
        </div>
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

</div>

<footer class="mt-auto">
    @include('shared.footer')
</footer>

<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="vendor/pace-js/pace.min.js"></script>
<script src="scripts/clientverse.js?{{config('app.version')}}"></script>
@include('shared.service-worker')

@yield('scripts')
</body>
</html>
