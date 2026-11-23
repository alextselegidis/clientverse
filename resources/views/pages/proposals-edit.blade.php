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
    {{ $proposal->exists ? __('edit') . ' ' . $proposal->number : __('create') . ' ' . __('proposal') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('proposals'), 'url' => route('proposals')],
        // An existing record links back to its page, so the trail leads out of the form.
        ...($proposal->exists
            ? [['label' => $proposal->number, 'url' => route('proposals.show', $proposal->id)], ['label' => __('edit')]]
            : [['label' => __('create')]]),
    ]])
@endsection

@section('navActions')
    @if($proposal->exists)
        <a href="{{ route('proposals.show', $proposal->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-eye me-2"></i>
            {{ __('view') }}
        </a>
        <form action="{{ route('proposals.destroy', $proposal->id) }}"
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
            <form action="{{ $proposal->exists ? route('proposals.update', $proposal->id) : route('proposals.store') }}" method="POST" id="edit-form">
                @csrf
                @if($proposal->exists)
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
                                <option value="{{ $customer->value }}" {{ old('customer_id', $proposal->customer_id) == $customer->value ? 'selected' : '' }}>
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
                               value="{{ old('number', $proposal->number) }}"
                               placeholder="{{ __('assigned_automatically') }}">
                        @error('number')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(array_keys(\App\Models\Proposal::statuses()) as $option)
                                <option value="{{ $option }}" {{ old('status', $proposal->status ?? 'draft') === $option ? 'selected' : '' }}>
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
                               value="{{ old('issue_date', $proposal->issue_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="open_till" class="form-label">{{ __('open_till') }}</label>
                        <input type="date" id="open_till" name="open_till" class="form-control"
                               value="{{ old('open_till', $proposal->open_till?->format('Y-m-d')) }}">
                        @error('open_till')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="currency" class="form-label">{{ __('currency') }}</label>
                        <input type="text" id="currency" name="currency" class="form-control" maxlength="3" required
                               value="{{ old('currency', $proposal->currency ?? default_currency()) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="subject" class="form-label">
                            {{ __('subject') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" class="form-control" maxlength="255" required
                               value="{{ old('subject', $proposal->subject) }}">
                        @error('subject')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sale_id" class="form-label">{{ __('sale') }}</label>
                        <select id="sale_id" name="sale_id" class="form-select">
                            <option value="">-</option>
                            @foreach($sales as $sale)
                                <option value="{{ $sale->value }}" {{ old('sale_id', $proposal->sale_id) == $sale->value ? 'selected' : '' }}>
                                    {{ $sale->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">{{ __('proposal_content') }}</label>
                    <textarea id="content" name="content" class="form-control" rows="8">{{ old('content', $proposal->content) }}</textarea>
                </div>

                @include('shared.line-items-editor', ['document' => $proposal])

                @if($proposal->exists)
                    @include('shared.document-totals', ['document' => $proposal, 'class' => 'mb-4'])
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="notes" class="form-label">{{ __('notes') }}</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $proposal->notes) }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="terms" class="form-label">{{ __('terms') }}</label>
                        <textarea id="terms" name="terms" class="form-control" rows="3">{{ old('terms', $proposal->terms) }}</textarea>
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
