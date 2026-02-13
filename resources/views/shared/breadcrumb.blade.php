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

@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small flex-nowrap overflow-hidden">
            <li class="breadcrumb-item flex-shrink-0">
                <a href="{{ route('dashboard') }}" class="text-decoration-none">
                    <i class="bi bi-house"></i>
                </a>
            </li>

            @foreach($breadcrumbs as $breadcrumb)

                @if($loop->last)
                    <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 150px;">
                        {{ $breadcrumb['label'] }}
                    </li>
                @elseif(isset($breadcrumb['url']))
                    <li class="breadcrumb-item text-truncate flex-shrink-0" style="max-width: 100px;">
                        <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">
                            {{ $breadcrumb['label'] }}
                        </a>
                    </li>
                @else
                    <li class="breadcrumb-item text-truncate flex-shrink-0" style="max-width: 100px;">
                        {{ $breadcrumb['label'] }}
                    </li>
                @endif

            @endforeach
        </ol>
    </nav>
@endif
