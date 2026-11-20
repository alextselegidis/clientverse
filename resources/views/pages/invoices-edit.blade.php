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
    {{ $invoice->exists ? __('edit') . ' ' . $invoice->number : __('create') . ' ' . __('invoice') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('invoices'), 'url' => route('invoices')],
        ['label' => $invoice->exists ? $invoice->number : __('create')]
    ]])
@endsection

@section('navActions')
    @if($invoice->exists)
        <a href="{{ route('invoices.show', $invoice->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-eye me-2"></i>
            {{ __('view') }}
        </a>
        <form action="{{ route('invoices.destroy', $invoice->id) }}"
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

@php
    // Old input wins after a failed save; otherwise the stored lines plus one blank row.
    $lines = old('items', $invoice->itemRows());
    $lines = array_values($lines);
    $lines[] = ['description' => '', 'quantity' => 1, 'unit_price' => '', 'tax_rate' => 0];
@endphp

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $invoice->exists ? route('invoices.update', $invoice->id) : route('invoices.store') }}" method="POST" id="edit-form">
                @csrf
                @if($invoice->exists)
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
                                <option value="{{ $customer->value }}" {{ old('customer_id', $invoice->customer_id) == $customer->value ? 'selected' : '' }}>
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
                               value="{{ old('number', $invoice->number) }}"
                               placeholder="{{ __('assigned_automatically') }}">
                        @error('number')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(['draft', 'sent', 'cancelled'] as $option)
                                <option value="{{ $option }}" {{ old('status', $invoice->status ?? 'draft') === $option ? 'selected' : '' }}>
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
                               value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="due_date" class="form-label">{{ __('due_date') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control"
                               value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                        @error('due_date')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">{{ __('currency') }}</label>
                        <input type="text" id="currency" name="currency" class="form-control" maxlength="3" required
                               value="{{ old('currency', $invoice->currency ?? default_currency()) }}">
                    </div>
                </div>

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
                        <tbody id="invoice-lines">
                        @foreach($lines as $i => $line)
                            @include('shared.line-item', ['index' => $i, 'line' => $line])
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <template id="invoice-line-template">
                    @include('shared.line-item', ['index' => '__INDEX__', 'line' => ['quantity' => 1, 'tax_rate' => 0]])
                </template>

                <button type="button" class="btn btn-sm btn-light border mb-4"
                        data-add-row="invoice-line-template" data-target="invoice-lines">
                    <i class="bi bi-plus me-1"></i>{{ __('add_line') }}
                </button>

                @if($invoice->exists)
                    @include('shared.document-totals', ['document' => $invoice, 'class' => 'mb-4'])
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="notes" class="form-label">{{ __('notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="terms" class="form-label">{{ __('terms') }}</label>
                        <textarea id="terms" name="terms" class="form-control" rows="3">{{ old('terms', $invoice->terms) }}</textarea>
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
