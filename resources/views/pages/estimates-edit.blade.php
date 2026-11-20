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
    {{ $estimate->exists ? __('edit') . ' ' . $estimate->number : __('create') . ' ' . __('estimate') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('estimates'), 'url' => route('estimates')],
        ['label' => $estimate->exists ? $estimate->number : __('create')]
    ]])
@endsection

@section('navActions')
    @if($estimate->exists)
        <a href="{{ route('estimates.show', $estimate->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-eye me-2"></i>
            {{ __('view') }}
        </a>
        <form action="{{ route('estimates.destroy', $estimate->id) }}"
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
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $estimate->exists ? route('estimates.update', $estimate->id) : route('estimates.store') }}" method="POST" id="edit-form">
                @csrf
                @if($estimate->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_id" class="form-label">
                            {{ __('customer') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">{{ __('select') }}...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->value }}" {{ old('customer_id', $estimate->customer_id) == $customer->value ? 'selected' : '' }}>
                                    {{ $customer->label }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="number" class="form-label">{{ __('number') }}</label>
                        <input type="text" id="number" name="number" class="form-control" maxlength="50"
                               value="{{ old('number', $estimate->number) }}"
                               placeholder="{{ __('assigned_automatically') }}">
                        @error('number')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(array_keys(\App\Models\Estimate::statuses()) as $option)
                                <option value="{{ $option }}" {{ old('status', $estimate->status ?? 'draft') === $option ? 'selected' : '' }}>
                                    {{ __($option) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="issue_date" class="form-label">{{ __('issue_date') }}</label>
                        <input type="date" id="issue_date" name="issue_date" class="form-control"
                               value="{{ old('issue_date', $estimate->issue_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="expiry_date" class="form-label">{{ __('expiry_date') }}</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control"
                               value="{{ old('expiry_date', $estimate->expiry_date?->format('Y-m-d')) }}">
                        @error('expiry_date')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">{{ __('currency') }}</label>
                        <input type="text" id="currency" name="currency" class="form-control" maxlength="3" required
                               value="{{ old('currency', $estimate->currency ?? default_currency()) }}">
                    </div>
                </div>

                @include('shared.line-items-editor', ['document' => $estimate])

                @if($estimate->exists)
                    @include('shared.document-totals', ['document' => $estimate, 'class' => 'mb-4'])
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="notes" class="form-label">{{ __('notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $estimate->notes) }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="terms" class="form-label">{{ __('terms') }}</label>
                        <textarea id="terms" name="terms" class="form-control" rows="3">{{ old('terms', $estimate->terms) }}</textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
            <button type="submit" form="edit-form" class="btn btn-primary">
                {{ __('save') }}
            </button>
        </div>
    </div>
@endsection
