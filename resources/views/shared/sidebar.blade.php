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
{{-- The app navigation: a fixed column from lg up, an off-canvas drawer below it (Bootstrap's offcanvas-lg). --}}
@php
    $user = Auth::user();
    // [label, route, active patterns, icon, permission module or null]
    $groups = [
        null => [
            ['dashboard', 'dashboard', ['dashboard'], 'house', null],
            ['calendar', 'calendar', ['calendar'], 'calendar3', null],
        ],
        'customers' => [
            ['customers', 'customers', ['customers*'], 'people', 'customers'],
            ['tickets', 'tickets', ['tickets*'], 'life-preserver', 'tickets'],
            ['knowledge_base', 'articles', ['articles*'], 'journal-text', 'articles'],
            ['surveys', 'surveys', ['surveys*'], 'ui-checks', 'surveys'],
        ],
        'work' => [
            ['projects', 'projects', ['projects*'], 'kanban', 'projects'],
            ['tasks', 'tasks', ['tasks*'], 'check2-square', 'tasks'],
        ],
        'sales' => [
            ['sales', 'sales', ['sales*'], 'graph-up-arrow', 'sales'],
            ['proposals', 'proposals', ['proposals*'], 'file-earmark-richtext', 'proposals'],
            ['estimates', 'estimates', ['estimates*'], 'file-earmark-ruled', 'estimates'],
            ['contracts', 'contracts', ['contracts*'], 'file-earmark-text', 'contracts'],
        ],
        'billing' => [
            ['invoices', 'invoices', ['invoices*'], 'receipt', 'invoices'],
            ['subscriptions', 'subscriptions', ['subscriptions*'], 'arrow-repeat', 'subscriptions'],
            ['credit_notes', 'credit-notes', ['credit-notes*'], 'receipt-cutoff', 'credit_notes'],
            ['expenses', 'expenses', ['expenses*'], 'wallet2', 'expenses'],
            ['items', 'items', ['items*'], 'box', 'items'],
        ],
        'insights' => [
            ['reports', 'reports', ['reports*'], 'bar-chart', 'reports'],
            ['goals', 'goals', ['goals*'], 'bullseye', 'reports'],
        ],
    ];
@endphp
<aside class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="sidebar" aria-label="{{ __('navigation') }}">
    <div class="app-sidebar-brand">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
            <img src="images/logo.png" alt="" width="28" height="28" class="logo-on-light me-2">
            <img src="images/logo-light.svg" alt="" width="28" height="28" class="logo-on-dark me-2">
            <span class="fw-bold fs-5 text-body">Clientverse</span>
        </a>
        <button type="button" class="btn-close d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"
                aria-label="{{ __('close') }}"></button>
    </div>

    <nav class="app-sidebar-nav">
        @foreach($groups as $group => $items)
            @php($items = array_filter($items, fn ($item) => !$item[4] || $user->hasPermission($item[4])))
            @continue(!$items)
            @if($group)
                <div class="app-sidebar-heading">{{ __($group) }}</div>
            @endif
            @foreach($items as [$label, $route, $patterns, $icon])
                @php($active = request()->routeIs(...$patterns))
                <a href="{{ route($route) }}" class="app-sidebar-link {{ $active ? 'active' : '' }}" title="{{ __($label) }}"
                   @if($active) aria-current="page" @endif>
                    <i class="bi bi-{{ $icon }}"></i>
                    <span>{{ __($label) }}</span>
                </a>
            @endforeach
        @endforeach

        @if($user->isAdmin())
            <div class="app-sidebar-heading">{{ __('setup') }}</div>
            <a href="{{ route('setup.localization') }}" title="{{ __('settings') }}"
               class="app-sidebar-link {{ request()->routeIs('setup.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>{{ __('settings') }}</span>
            </a>
        @endif
    </nav>
</aside>
