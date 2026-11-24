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
{{-- One swipeable row below lg, a column beside the page from lg --}}
<ul id="setup-nav" class="nav flex-row flex-nowrap flex-lg-column overflow-x-auto gap-2 gap-lg-0 mb-3 mb-lg-0 text-nowrap">
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.localization*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.localization') }}">
            <i class="bi bi-globe me-2 me-lg-3 text-muted"></i>
            {{ __('localization') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.billing*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.billing') }}">
            <i class="bi bi-envelope me-2 me-lg-3 text-muted"></i>
            {{ __('billing_settings') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.users*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.users') }}">
            <i class="bi bi-people me-2 me-lg-3 text-muted"></i>
            {{ __('users') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.custom-fields*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.custom-fields') }}">
            <i class="bi bi-input-cursor-text me-2 me-lg-3 text-muted"></i>
            {{ __('custom_fields') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.departments*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.departments') }}">
            <i class="bi bi-diagram-3 me-2 me-lg-3 text-muted"></i>
            {{ __('departments') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.announcements*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.announcements') }}">
            <i class="bi bi-megaphone me-2 me-lg-3 text-muted"></i>
            {{ __('announcements') }}
        </a>
    </li>
    <li class="nav-item flex-shrink-0">
        <a class="nav-link px-2 px-lg-0 py-2 d-flex align-items-center {{ request()->routeIs('setup.activity*') ? 'text-primary fw-medium' : 'text-body' }}"
           href="{{ route('setup.activity') }}">
            <i class="bi bi-clock-history me-2 me-lg-3 text-muted"></i>
            {{ __('activity_log') }}
        </a>
    </li>
</ul>
