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
    {{ $sale->exists ? __('edit_sale') : __('create') . ' ' . __('sale') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('sales'), 'url' => route('sales')],
        ['label' => $sale->exists ? $sale->name : __('create')]
    ]])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ $sale->exists ? route('sales.update', $sale->id) : route('sales.store') }}" method="POST">
                        @csrf
                        @if($sale->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    {{ __('name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       value="{{ old('name', $sale->name) }}">
                                @error('name')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">
                                    {{ __('customer') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">{{ __('select') }}...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->value }}" {{ old('customer_id', $sale->customer_id) == $customer->value ? 'selected' : '' }}>
                                            {{ $customer->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="value" class="form-label">{{ __('value') }}</label>
                                <input type="number" id="value" name="value" class="form-control" step="0.01"
                                       value="{{ old('value', $sale->value) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">{{ __('currency') }}</label>
                                <input type="text" id="currency" name="currency" class="form-control" maxlength="3"
                                       value="{{ old('currency', $sale->currency ?? 'USD') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="probability" class="form-label">{{ __('probability') }} (%)</label>
                                <input type="number" id="probability" name="probability" class="form-control" min="0" max="100"
                                       value="{{ old('probability', $sale->probability ?? 0) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stage" class="form-label">{{ __('stage') }}</label>
                                <select id="stage" name="stage" class="form-select">
                                    @foreach(\App\Models\Sale::stages() as $key => $label)
                                        <option value="{{ $key }}" {{ old('stage', $sale->stage ?? 'lead') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expected_close_date" class="form-label">{{ __('expected_value') }} {{ __('due_date') }}</label>
                                <input type="date" id="expected_close_date" name="expected_close_date" class="form-control"
                                       value="{{ old('expected_close_date', $sale->expected_close_date?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('notes') }}</label>
                            <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes', $sale->notes) }}</textarea>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ $sale->exists ? route('sales.show', $sale->id) : route('sales') }}" class="btn btn-outline-secondary">
                                {{ __('cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
