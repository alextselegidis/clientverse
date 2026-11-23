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
{{-- The custom field inputs of a record form: @include('shared.custom-fields-edit', ['record' => $customer]) --}}
@php
    $fields = \App\Models\CustomField::for($record->getTable());
@endphp
@if($fields->isNotEmpty())
    <div class="row">
        @foreach($fields as $field)
            @php
                $name = 'custom_values[' . $field->id . ']';
                $id = 'custom-field-' . $field->id;
                $value = old('custom_values.' . $field->id, $record->custom_values[$field->id] ?? null);
            @endphp
            <div class="{{ $field->type === 'textarea' ? 'col-12' : 'col-md-6' }} mb-3">
                @if($field->type === 'checkbox')
                    <input type="hidden" name="{{ $name }}" value="0">
                    <div class="form-check mt-md-4">
                        <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" class="form-check-input" @checked($value)>
                        <label for="{{ $id }}" class="form-check-label">{{ $field->label }}</label>
                    </div>
                @else
                    <label for="{{ $id }}" class="form-label">
                        {{ $field->label }}
                        @if($field->is_required)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    @switch($field->type)
                        @case('textarea')
                            <textarea id="{{ $id }}" name="{{ $name }}" class="form-control" rows="3" maxlength="5000"
                                      @required($field->is_required)>{{ $value }}</textarea>
                            @break
                        @case('select')
                            <select id="{{ $id }}" name="{{ $name }}" class="form-select" @required($field->is_required)>
                                <option value="">-</option>
                                @foreach($field->options ?? [] as $option)
                                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @break
                        @default
                            <input type="{{ $field->type }}" id="{{ $id }}" name="{{ $name }}" class="form-control"
                                   value="{{ $value }}" @required($field->is_required)
                                   @if($field->type === 'number') step="any" @elseif($field->type === 'text') maxlength="255" @endif>
                    @endswitch
                @endif
                @error('custom_values.' . $field->id)
                <span class="form-text text-danger">{{ $message }}</span>
                @enderror
            </div>
        @endforeach
    </div>
@endif
