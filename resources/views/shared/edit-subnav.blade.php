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
@endphp
<ul class="nav nav-tabs mb-4">
    @foreach($items as $item)
        <li class="nav-item">
            <a class="nav-link {{ $activeRoute === $item['route'] ? 'active' : '' }}"
               href="{{ route($item['route'], $item['params'] ?? []) }}">
                @if(isset($item['icon']))
                    <i class="bi bi-{{ $item['icon'] }} me-1"></i>
                @endif
                {{ $item['label'] }}
            </a>
        </li>
    @endforeach
</ul>
