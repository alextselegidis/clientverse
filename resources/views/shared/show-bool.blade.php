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
            <span class="badge bg-success-subtle text-success">{{ __('yes') }}</span>
        @else
            <span class="badge bg-danger-subtle text-danger">{{ __('no') }}</span>
        @endif
    </div>
</div>
