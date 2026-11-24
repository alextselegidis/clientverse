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
{{-- Sortable Created and Modified headers for a list table; pair with shared.timestamp-cells.
     They show from xl (pass $tier = 'xxl' on a list that is already wide), a fourth tier after the lg columns. --}}
@foreach(['created_at' => __('created'), 'updated_at' => __('modified')] as $column => $label)
    <th class="d-none d-{{ $tier ?? 'xl' }}-table-cell text-nowrap">
        <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => request('sort') === $column && request('direction') === 'asc' ? 'desc' : 'asc', 'page' => null]) }}"
           class="text-decoration-none">
            {{ $label }}
            @if(request('sort') === $column)
                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
            @endif
        </a>
    </th>
@endforeach
