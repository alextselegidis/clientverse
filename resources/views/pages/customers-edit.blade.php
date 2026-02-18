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
    {{ $customer->exists ? __('edit_customer') : __('create') . ' ' . __('customer') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers'), 'url' => route('customers')],
        ['label' => $customer->exists ? $customer->name : __('create')]
    ]])
@endsection

@section('navActions')
    @if($customer->exists)
        <a href="{{ route('customers.create') }}" class="nav-link me-lg-3">
            <i class="bi bi-plus-square me-2"></i>
            {{ __('add') }}
        </a>
        <form action="{{ route('customers.destroy', $customer->id) }}"
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
    @if($customer->exists)
        <div class="d-flex flex-column flex-lg-row gap-4">
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'customers.edit', 'params' => ['customer' => $customer->id], 'icon' => 'file-text']
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
            <form action="{{ $customer->exists ? route('customers.update', $customer->id) : route('customers.store') }}" method="POST" id="edit-form">
                @csrf
                @if($customer->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            {{ __('name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required autofocus
                               value="{{ old('name', $customer->name) }}">
                        @error('name')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="company" class="form-label">{{ __('company') }}</label>
                        <input type="text" id="company" name="company" class="form-control"
                               value="{{ old('company', $customer->company) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">{{ __('type') }}</label>
                        <select id="type" name="type" class="form-select">
                            @foreach(\App\Models\Customer::types() as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $customer->type) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(\App\Models\Customer::statuses() as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $customer->status) == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">{{ __('email') }}</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email', $customer->email) }}">
                        @error('email')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">{{ __('phone') }}</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                               value="{{ old('phone', $customer->phone) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="website" class="form-label">{{ __('website') }}</label>
                        <input type="url" id="website" name="website" class="form-control"
                               value="{{ old('website', $customer->website) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="vat_id" class="form-label">{{ __('vat_id') }}</label>
                        <input type="text" id="vat_id" name="vat_id" class="form-control"
                               value="{{ old('vat_id', $customer->vat_id) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label">{{ __('currency') }}</label>
                        <input type="text" id="currency" name="currency" class="form-control" maxlength="3"
                               value="{{ old('currency', $customer->currency ?? 'USD') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">{{ __('address') }}</label>
                    <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $customer->address) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="billing_address" class="form-label">{{ __('billing_address') }}</label>
                    <textarea id="billing_address" name="billing_address" class="form-control" rows="2">{{ old('billing_address', $customer->billing_address) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes', $customer->notes) }}</textarea>
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
