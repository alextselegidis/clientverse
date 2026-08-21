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

{{--
    @var string $icon Bootstrap icon name, without the "bi-" prefix
    @var string $label Section heading
    @var array $section Keys: total, items, url
--}}

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="mb-0">
        <i class="bi bi-{{ $icon }} me-2 text-primary"></i>
        {{ $label }}
        <span class="badge bg-secondary ms-2">{{ $section['total'] }}</span>
    </h5>

    @if($section['total'] > $section['items']->count())
        <a href="{{ $section['url'] }}" class="small text-decoration-none d-inline-block py-1">
            {{ __('view_all') }}
            <i class="bi bi-arrow-right ms-1"></i>
        </a>
    @endif
</div>
