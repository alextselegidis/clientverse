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

<div class="mb-3">
    <label class="form-label text-dark small fw-medium mb-1">{{ $label }}</label>
    <div>
        @if($value)
            <a href="{{ $href ?? '#' }}" class="text-decoration-none" {{ isset($target) ? 'target=' . $target : '' }}>
                {{ $value }}
                @if(str_starts_with($href ?? '', 'http'))
                    <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                @endif
            </a>
        @else
            -
        @endif
    </div>
</div>
