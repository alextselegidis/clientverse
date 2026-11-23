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
    {{ $creditNote->exists ? __('edit') . ' ' . $creditNote->number : __('create') . ' ' . __('credit_note') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('credit_notes'), 'url' => route('credit-notes')],
        ['label' => $creditNote->exists ? $creditNote->number : __('create')]
    ]])
@endsection

@section('navActions')
    @if($creditNote->exists)
        <a href="{{ route('credit-notes.show', $creditNote->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-eye me-2"></i>
            {{ __('view') }}
        </a>
        <form action="{{ route('credit-notes.destroy', $creditNote->id) }}"
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
            <form action="{{ $creditNote->exists ? route('credit-notes.update', $creditNote->id) : route('credit-notes.store') }}" method="POST" id="edit-form">
                @csrf
                @if($creditNote->exists)
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
                                <option value="{{ $customer->value }}" {{ old('customer_id', $creditNote->customer_id) == $customer->value ? 'selected' : '' }}>
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
                               value="{{ old('number', $creditNote->number) }}"
                               placeholder="{{ __('assigned_automatically') }}">
                        @error('number')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(array_keys(\App\Models\CreditNote::statuses()) as $option)
                                <option value="{{ $option }}" {{ old('status', $creditNote->status ?? 'draft') === $option ? 'selected' : '' }}>
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
                               value="{{ old('issue_date', $creditNote->issue_date?->format('Y-m-d')) }}">
                    </div>


                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">{{ __('currency') }}</label>
                        <input type="text" id="currency" name="currency" class="form-control" maxlength="3" required
                               value="{{ old('currency', $creditNote->currency ?? default_currency()) }}">
                    </div>
                </div>

                @include('shared.line-items-editor', ['document' => $creditNote])

                @if($creditNote->exists)
                    @include('shared.document-totals', ['document' => $creditNote, 'class' => 'mb-4'])
                @endif

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="notes" class="form-label">{{ __('notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $creditNote->notes) }}</textarea>
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
