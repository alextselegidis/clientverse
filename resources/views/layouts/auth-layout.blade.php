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
<body class="bg-light auth-layout">

<div class="d-flex justify-content-center align-items-center min-vh-100">

    <div class="bg-white w-100 rounded-0 rounded-lg-3 shadow-lg-sm py-lg-4" style="max-width: 500px;">

        <div class="text-center mt-3 mt-lg-5 mb-3">
            <img src="images/logo.png" alt="logo" class="public-logo-image" style="width: 128px"/>
        </div>

        @yield('content')

        <div class="text-center small my-5">
            Powered By
            <a href="https://github.com/alextselegidis/clientverse" target="_blank">
                Clientverse
            </a>
        </div>

    </div>

</div>

<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="vendor/pace-js/pace.min.js"></script>
<script src="scripts/clientverse.js?{{config('app.version')}}"></script>
@include('shared.service-worker')

@yield('scripts')
</body>
</html>
