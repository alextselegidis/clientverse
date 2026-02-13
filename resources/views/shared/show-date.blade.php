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
            {{ $value->locale(app()->getLocale())->isoFormat('L LT') }}
        @else
            -
        @endif
    </div>
</div>
