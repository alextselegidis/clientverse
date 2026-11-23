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
@extends('layouts.main-layout')

@section('pageTitle')
    {{ $field->exists ? $field->label : __('create') . ' ' . __('custom_field') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('custom_fields'), 'url' => route('setup.custom-fields')],
        ['label' => $field->exists ? $field->label : __('create')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ $field->exists ? route('setup.custom-fields.update', $field->id) : route('setup.custom-fields.store') }}"
                          method="POST" id="edit-form">
                        @csrf
                        @if($field->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="label" class="form-label">{{ __('label') }} <span class="text-danger">*</span></label>
                                <input type="text" id="label" name="label" class="form-control" required autofocus maxlength="255"
                                       value="{{ old('label', $field->label) }}">
                                @error('label')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="applies_to" class="form-label">{{ __('record') }}</label>
                                <select id="applies_to" name="applies_to" class="form-select" @disabled($field->exists)>
                                    @foreach(\App\Models\CustomField::appliesToOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('applies_to', $field->applies_to) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if($field->exists)
                                    <input type="hidden" name="applies_to" value="{{ $field->applies_to }}">
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">{{ __('type') }}</label>
                                <select id="type" name="type" class="form-select">
                                    @foreach(\App\Models\CustomField::types() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', $field->type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">{{ __('position') }}</label>
                                <input type="number" id="position" name="position" class="form-control" min="0" max="9999"
                                       value="{{ old('position', $field->position ?? 0) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="options" class="form-label">{{ __('options') }}</label>
                            <textarea id="options" name="options" class="form-control" rows="4">{{ old('options', implode("\n", $field->options ?? [])) }}</textarea>
                            <div class="form-text">{{ __('custom_field_options_hint') }}</div>
                            @error('options')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" id="is_required" name="is_required" value="1" class="form-check-input"
                                   @checked(old('is_required', $field->is_required))>
                            <label for="is_required" class="form-check-label">{{ __('required') }}</label>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
