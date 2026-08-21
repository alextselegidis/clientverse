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

@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small flex-nowrap overflow-x-auto">
            <li class="breadcrumb-item flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <i class="bi bi-house"></i>
                </a>
            </li>

            @foreach($breadcrumbs as $breadcrumb)

                {{-- A deep trail does not fit on a phone: keep the parent and the current
                     page, and reveal the levels above them from the sm breakpoint up. --}}
                @php
                    $collapsed = $loop->remaining > 1 ? 'd-none d-sm-flex' : '';
                @endphp

                @if($loop->last)
                    <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 150px;">
                        {{ $breadcrumb['label'] }}
                    </li>
                @elseif(isset($breadcrumb['url']))
                    <li class="breadcrumb-item flex-shrink-0 {{ $collapsed }}">
                        <a href="{{ $breadcrumb['url'] }}"
                           class="text-decoration-none d-inline-block text-truncate align-bottom"
                           style="max-width: 160px;">
                            {{ $breadcrumb['label'] }}
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item text-truncate flex-shrink-0 {{ $collapsed }}" style="max-width: 160px;">
                        {{ $breadcrumb['label'] }}
                    </li>
                @endif

            @endforeach
        </ol>
    </nav>
@endif
