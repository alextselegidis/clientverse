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
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('page_title') | Clientverse</title>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="styles/clientverse.css?{{config('app.version')}}">

    @yield('styles')
</head>
<body class="bg-light">

<div class="container">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">
            <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center">
                <div class="text-center">
                    <img src="images/logo.png" alt="logo" class="public-logo-image mb-3"/>
                </div>

                <div class="p-4 bg-white shadow-md overflow-hidden w-100">
                    @yield('content')

                    <div class="text-center small mt-3">
                        Powered By
                        <a href="https://clientverse.org" target="_blank">
                            Clientverse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="scripts/clientverse.js?{{config('app.version')}}"></script>

@yield('scripts')
</body>
</html>
