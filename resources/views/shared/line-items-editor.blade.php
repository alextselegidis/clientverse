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
{{-- The editable lines of an invoice or estimate; posts as items[n][description|quantity|unit_price|tax_rate]. --}}
@php
    // Old input wins after a failed save; otherwise the stored lines plus one blank row.
    $lines = array_values(old('items', $document->itemRows()));
    $lines[] = ['description' => '', 'quantity' => 1, 'unit_price' => '', 'tax_rate' => 0];
    // ponytail: the whole catalog goes into a <datalist>; switch to a search endpoint past a few thousand items.
    $catalog = \App\Models\Item::orderBy('description')->get();
@endphp
<h6 class="mt-3 mb-2">{{ __('line_items') }}</h6>
<p class="form-text mt-0">{{ __('line_items_hint') }}</p>

<div class="table-responsive mb-2">
    <table class="table table-light-header align-middle mb-0">
        <thead>
        <tr>
            <th style="min-width: 220px;">{{ __('description') }}</th>
            <th style="width: 110px;">{{ __('quantity') }}</th>
            <th style="width: 140px;">{{ __('unit_price') }}</th>
            <th style="width: 110px;">{{ __('tax') }} %</th>
        </tr>
        </thead>
        <tbody id="line-items" data-catalog="item-catalog">
        @foreach($lines as $i => $line)
            @include('shared.line-item', ['index' => $i, 'line' => $line])
        @endforeach
        </tbody>
    </table>
</div>

<template id="line-item-template">
    @include('shared.line-item', ['index' => '__INDEX__', 'line' => ['quantity' => 1, 'tax_rate' => 0]])
</template>

<button type="button" class="btn btn-sm btn-light border mb-4"
        data-add-row="line-item-template" data-target="line-items">
    <i class="bi bi-plus me-1"></i>{{ __('add_line') }}
</button>

<datalist id="item-catalog">
    @foreach($catalog as $item)
        <option value="{{ $item->description }}" data-unit-price="{{ $item->unit_price }}" data-tax-rate="{{ $item->tax_rate }}"></option>
    @endforeach
</datalist>
