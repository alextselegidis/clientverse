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
    {{ $expense->exists ? $expense->description : __('create') . ' ' . __('expense') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('expenses'), 'url' => route('expenses')],
        ['label' => $expense->exists ? $expense->description : __('create')]
    ]])
@endsection

@section('navActions')
    @if($expense->exists)
        @if($expense->invoice)
            <a href="{{ route('invoices.show', $expense->invoice_id) }}" class="nav-link me-lg-3">
                <i class="bi bi-receipt me-2"></i>
                {{ $expense->invoice->number }}
            </a>
        @elseif($expense->billable && $expense->customer_id)
            <form action="{{ route('expenses.bill', $expense->id) }}" method="POST" class="d-inline me-lg-3">
                @csrf
                <button type="submit" class="nav-link">
                    <i class="bi bi-receipt me-2"></i>
                    {{ __('bill_to_customer') }}
                </button>
            </form>
        @endif
        <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST"
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $expense->exists ? route('expenses.update', $expense->id) : route('expenses.store') }}" method="POST" id="edit-form">
                        @csrf
                        @if($expense->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('description') }} <span class="text-danger">*</span></label>
                            <input type="text" id="description" name="description" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('description', $expense->description) }}">
                            @error('description')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="expense_date" class="form-label">{{ __('date') }} <span class="text-danger">*</span></label>
                                <input type="date" id="expense_date" name="expense_date" class="form-control" required
                                       value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="amount" class="form-label">{{ __('amount') }} <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required
                                       value="{{ old('amount', $expense->amount) }}">
                                @error('amount')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">{{ __('currency') }}</label>
                                <input type="text" id="currency" name="currency" class="form-control" maxlength="3" required
                                       value="{{ old('currency', $expense->currency ?? default_currency()) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">{{ __('category') }}</label>
                                <input type="text" id="category" name="category" class="form-control" maxlength="100" list="expense-categories"
                                       value="{{ old('category', $expense->category) }}">
                                <datalist id="expense-categories">
                                    @foreach($categories as $option)
                                        <option value="{{ $option }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="customer_id" class="form-label">{{ __('customer') }}</label>
                                <select id="customer_id" name="customer_id" class="form-select">
                                    <option value="">-</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->value }}" @selected(old('customer_id', $expense->customer_id) == $customer->value)>{{ $customer->label }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="project_id" class="form-label">{{ __('project') }}</label>
                                <select id="project_id" name="project_id" class="form-select">
                                    <option value="">-</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->value }}" @selected(old('project_id', $expense->project_id) == $project->value)>{{ $project->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="billable" value="0">
                            <input type="checkbox" id="billable" name="billable" value="1" class="form-check-input"
                                   @checked(old('billable', $expense->billable))>
                            <label for="billable" class="form-check-label">{{ __('billable_to_customer') }}</label>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('notes') }}</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $expense->notes) }}</textarea>
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
