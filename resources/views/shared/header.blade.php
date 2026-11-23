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
{{-- The top bar: menu button on small screens, search, the Add menu and the account menu. --}}
@php
    $user = Auth::user();
    // [label, route, icon, permission module]
    $quickAdd = array_filter([
        ['customer', 'customers.create', 'person-plus', 'customers'],
        ['project', 'projects.create', 'kanban', 'projects'],
        ['task', 'tasks.create', 'check2-square', 'tasks'],
        ['sale', 'sales.create', 'graph-up-arrow', 'sales'],
        ['invoice', 'invoices.create', 'receipt', 'invoices'],
        ['ticket', 'tickets.create', 'life-preserver', 'tickets'],
    ], fn ($item) => $user->hasPermission($item[3], 'edit'));
    $initials = collect(preg_split('/\s+/', trim($user->name)))->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<header class="app-topbar" id="header">
    <button type="button" class="btn btn-icon d-lg-none me-1" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
            aria-controls="sidebar" aria-label="{{ __('navigation') }}">
        <i class="bi bi-list fs-4"></i>
    </button>
    <button type="button" class="btn btn-icon d-none d-lg-inline-flex" data-sidebar-toggle
            aria-controls="sidebar" aria-label="{{ __('collapse_menu') }}" title="{{ __('collapse_menu') }}">
        <i class="bi bi-layout-sidebar fs-5"></i>
    </button>
    <a href="{{ route('dashboard') }}" class="d-lg-none me-2" aria-label="{{ __('dashboard') }}">
        <img class="logo-on-light" src="images/logo.png" alt="" width="28" height="28">
        <img class="logo-on-dark" src="images/logo-light.svg" alt="" width="28" height="28">
    </a>

    <button type="button" class="app-search-trigger" data-bs-toggle="modal" data-bs-target="#search-modal">
        <i class="bi bi-search"></i>
        <span class="d-none d-sm-inline">{{ __('search') }}...</span>
        <kbd class="d-none d-md-inline ms-auto">/</kbd>
    </button>

    <div class="d-flex align-items-center gap-2 ms-auto">
        @if($quickAdd)
            <div class="dropdown">
                <button type="button" class="btn btn-primary app-add-button" data-bs-toggle="dropdown" aria-expanded="false"
                        aria-label="{{ __('add') }}" title="{{ __('add') }}">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($quickAdd as [$label, $route, $icon])
                        <li>
                            <a class="dropdown-item" href="{{ route($route) }}">
                                <i class="bi bi-{{ $icon }} me-2"></i>{{ __($label) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dropdown">
            <button type="button" class="app-avatar" data-bs-toggle="dropdown" aria-expanded="false"
                    aria-label="{{ __('account') }}" title="{{ $user->name }}">
                {{ $initials ?: '?' }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2">
                    <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                    <div class="small text-muted text-truncate">{{ $user->email }}</div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('account') }}">
                        <i class="bi bi-person-circle me-2"></i>{{ __('account') }}
                    </a>
                </li>
                @if($user->isAdmin())
                    <li>
                        <a class="dropdown-item" href="{{ route('setup.localization') }}">
                            <i class="bi bi-gear me-2"></i>{{ __('setup') }}
                        </a>
                    </li>
                @endif
                <li>
                    <a class="dropdown-item" href="{{ route('about') }}">
                        <i class="bi bi-info-circle me-2"></i>{{ __('about') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="https://clientverse.org/premium" target="_blank">
                        <i class="bi bi-crown me-2"></i>{{ __('premium') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="px-3 pt-1 pb-2">
                    <form action="{{ route('account.theme') }}" method="POST">
                        @csrf
                        @method('PUT')
                        {{-- The hidden inputs sit outside the group, or Bootstrap squares the first button's corners --}}
                        <div class="btn-group w-100" role="group" aria-label="{{ __('theme') }}">
                        @foreach(['light' => 'sun', 'dark' => 'moon-stars', 'auto' => 'circle-half'] as $theme => $icon)
                            <button type="submit" name="theme" value="{{ $theme }}" title="{{ __('theme_' . $theme) }}"
                                    aria-label="{{ __('theme_' . $theme) }}"
                                    class="btn btn-sm btn-light border {{ $user->theme === $theme ? 'active' : '' }}">
                                <i class="bi bi-{{ $icon }}"></i>
                            </button>
                        @endforeach
                        </div>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout.perform') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i>{{ __('logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

<!-- Global Search Modal -->
<div class="modal fade" tabindex="-1" id="search-modal" aria-label="{{ __('search') }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('search') }}" method="GET" class="modal-body p-2">
                <div class="input-group input-group-lg app-search-input">
                    <span class="input-group-text border-0 bg-transparent">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="search" name="q" class="form-control border-0 shadow-none"
                           value="{{ request('q') }}"
                           aria-label="{{ __('search') }}"
                           placeholder="{{ __('search_everything') }}" autocomplete="off">
                </div>
                {{-- Spotlight: sidebar pages and live record matches, filled by clientverse.js --}}
                <div class="list-group list-group-flush app-spotlight" data-pages-label="{{ __('pages') }}" hidden></div>
            </form>
        </div>
    </div>
</div>
