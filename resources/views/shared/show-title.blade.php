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

<h1 class="mb-4 border-bottom pb-2 d-flex align-items-center gap-3 fs-3">
    @if ($icon ?? null)
        <img src="data:image/x-icon;base64,{{ $icon }}" width="40" height="40">
    @endif

    {{Str::limit($title, 60)}}
</h1>
