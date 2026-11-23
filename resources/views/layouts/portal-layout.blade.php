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
{{-- Client portal pages: a plain top bar with the company and the signed-in contact. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
    <meta name="robots" content="noindex">
</head>
<body class="bg-light">
<nav class="navbar bg-primary" data-bs-theme="dark">
    <div class="container" style="max-width: 960px;">
        <a href="{{ route('portal') }}" class="navbar-brand text-truncate">
            {{ setting('company_name') ?: config('app.name') }}
        </a>
        <form action="{{ route('portal.logout') }}" method="POST" class="d-flex align-items-center gap-3">
            @csrf
            <span class="text-white-50 small d-none d-md-inline">{{ Auth::guard('contact')->user()->full_name }}</span>
            <button type="submit" class="btn btn-sm btn-light border">
                <i class="bi bi-box-arrow-right me-1"></i>{{ __('log_out') }}
            </button>
        </form>
    </div>
</nav>

<main class="container py-4" style="max-width: 960px;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</main>

<script src="vendor/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
