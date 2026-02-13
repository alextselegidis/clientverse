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

@php use App\Models\User; @endphp
@php use App\Models\Setting; @endphp

<ul id="settings-nav" class="nav flex-column">
    @can('viewAny', Setting::class)
        <li class="nav-item">
            <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('settings') ? 'text-primary fw-medium' : 'text-body' }}" href="{{ route('settings') }}">
                <i class="bi bi-gear me-3 text-muted"></i>
                {{ __('settings') }}
            </a>
        </li>
    @endcan

    <li class="nav-item">
        <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('links*') ? 'text-primary fw-medium' : 'text-body' }}" href="{{ route('links') }}">
            <i class="bi bi-link-45deg me-3 text-muted"></i>
            {{ __('links') }}
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('tags*') ? 'text-primary fw-medium' : 'text-body' }}" href="{{ route('tags') }}">
            <i class="bi bi-tags me-3 text-muted"></i>
            {{ __('tags') }}
        </a>
    </li>

    @can('viewAny', User::class)
        <li class="nav-item">
            <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('users*') ? 'text-primary fw-medium' : 'text-body' }}" href="{{ route('users') }}">
                <i class="bi bi-people me-3 text-muted"></i>
                {{ __('users') }}
            </a>
        </li>
    @endcan
</ul>
