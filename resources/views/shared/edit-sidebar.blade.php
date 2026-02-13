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
@php
    $items = $items ?? [];
    $activeRoute = request()->route()->getName();
    $activeItem = collect($items)->first(fn($item) => $item['route'] === $activeRoute) ?? $items[0] ?? null;
@endphp
<!-- Mobile Dropdown -->
<div class="d-lg-none mb-3">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
            @if($activeItem)
                @if(isset($activeItem['icon']))
                    <span>
                        <i class="bi bi-{{ $activeItem['icon'] }} me-2"></i>
                        {{ $activeItem['label'] }}
                    </span>
                @else
                    <span>{{ $activeItem['label'] }}</span>
                @endif
            @else
                <span>{{ __('menu') }}</span>
            @endif
        </button>
        <ul class="dropdown-menu w-100">
            @foreach($items as $item)
                <li>
                    <a class="dropdown-item {{ $activeRoute === $item['route'] ? 'active' : '' }}"
                       href="{{ route($item['route'], $item['params'] ?? []) }}">
                        @if(isset($item['icon']))
                            <i class="bi bi-{{ $item['icon'] }} me-2"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
<!-- Desktop Sidebar -->
<div class="d-none d-lg-block">
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-3">
            <ul class="nav flex-column">
                @foreach($items as $item)
                    <li class="nav-item">
                        <a class="nav-link px-2 py-2 rounded d-flex align-items-center {{ $activeRoute === $item['route'] ? 'bg-primary text-white fw-medium' : 'text-body' }}"
                           href="{{ route($item['route'], $item['params'] ?? []) }}">
                            @if(isset($item['icon']))
                                <i class="bi bi-{{ $item['icon'] }} me-2 {{ $activeRoute === $item['route'] ? '' : 'text-muted' }}"></i>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
