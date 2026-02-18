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

@section('navActions')
    @if($sale->exists)
        <a href="{{ route('sales.create') }}" class="nav-link me-lg-3">
            <i class="bi bi-plus-square me-2"></i>
            {{ __('add') }}
        </a>
        <form action="{{ route('sales.destroy', $sale->id) }}"
              method="POST"
              onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="nav-link">
                <i class="bi bi-trash me-2"></i>
                {{ __('delete') }}
            </button>
        </form>
    @endif
@endsection

@section('content')
    @if($sale->exists)
        <div class="d-flex flex-column flex-lg-row gap-4">
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'sales.edit', 'params' => ['sale' => $sale->id], 'icon' => 'file-text']
                ]])
            </div>
            <!-- Main Content -->
            <div class="flex-grow-1">
    @else
        <div class="row justify-content-center">
            <div class="col-lg-8">
    @endif
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form action="{{ $sale->exists ? route('sales.update', $sale->id) : route('sales.store') }}" method="POST" id="edit-form">
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
                                <input type="text" id="name" name="name" class="form-control" required autofocus
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
                                       value="{{ old('currency', $sale->currency ?? default_currency()) }}">
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
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
