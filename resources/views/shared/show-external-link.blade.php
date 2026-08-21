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

<div class="mb-4">
    <h6 class="text-muted">
        {{$label}}
    </h6>
    @include('shared.external-link-value', [
        'value' => $value,
        'href' => $href ?? null,
    ])
</div>

