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
{{-- The custom field values of a record's detail page, two to a row. --}}
@php($pairs = $record->customFieldValues())
@if($pairs->isNotEmpty())
    <div class="row g-3 {{ $class ?? '' }}">
        @foreach($pairs as [$field, $value])
            <div class="{{ $field->type === 'textarea' ? 'col-12' : 'col-md-6' }}">
                <div class="text-muted small">{{ $field->label }}</div>
                <div class="{{ $field->type === 'textarea' ? 'text-break' : 'text-truncate' }}" style="white-space: pre-line;">{{ $field->format($value) ?: '-' }}</div>
            </div>
        @endforeach
    </div>
@endif
