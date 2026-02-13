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

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <base href="{{url('')}}/">

    <title>@yield('pageTitle') | Clientverse</title>
    <meta name="description" content="Clientverse is a bookmark management application designed to help users easily organize your web links in one place.">
    <meta name="theme-color" content="#e3434c">

    <link rel="icon" href="favicon.ico" type="image/x-icon" />

    <link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="vendor/pace-js/pace-theme-default.min.css">
    <link rel="stylesheet" href="vendor/pace-js/pace-theme-flat-top.tmpl.css">
    <link rel="stylesheet" href="styles/clientverse.css?{{config('app.version')}}">

    @yield('styles')
</head>
<body class="bg-light message-layout">

<div class="d-flex justify-content-center align-items-center min-vh-100">

    <div class="bg-white w-100 text-center rounded-0 rounded-lg-3 shadow-lg-sm py-lg-4" style="max-width: 500px;">

         <div class="text-center mt-5 mb-3">
             <img src="images/logo.png" alt="logo" class="public-logo-image mb-3" style="width: 128px"/>
         </div>

        @include('shared.errors', ['class' => 'mx-2 mx-lg-5 text-center'])

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

@yield('scripts')
</body>
</html>
