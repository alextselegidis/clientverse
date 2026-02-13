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
<ul id="setup-nav" class="nav flex-column">
    <li class="nav-item">
        <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.localization*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.localization') }}">
            <i class="bi bi-globe me-3 text-muted"></i>
            {{ __('localization') }}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link px-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.users*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.users') }}">
            <i class="bi bi-people me-3 text-muted"></i>
            {{ __('users') }}
        </a>
    </li>
</ul>
