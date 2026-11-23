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
    {{ $subscription->exists ? $subscription->name : __('create') . ' ' . __('subscription') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('subscriptions'), 'url' => route('subscriptions')],
        // An existing record links back to its page, so the trail leads out of the form.
        ...($subscription->exists
            ? [['label' => $subscription->name, 'url' => route('subscriptions.show', $subscription->id)], ['label' => __('edit')]]
            : [['label' => __('create')]]),
    ]])
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $subscription->exists ? route('subscriptions.update', $subscription->id) : route('subscriptions.store') }}"
                          method="POST" id="edit-form">
                        @csrf
                        @if($subscription->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __('name') }} <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required autofocus maxlength="255"
                                       value="{{ old('name', $subscription->name) }}" placeholder="{{ __('subscription_name_placeholder') }}">
                                @error('name')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">{{ __('customer') }} <span class="text-danger">*</span></label>
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">{{ __('select') }}...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->value }}" @selected(old('customer_id', $subscription->customer_id) == $customer->value)>{{ $customer->label }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 col-md-3 mb-3">
                                <label for="amount" class="form-label">{{ __('price') }} <span class="text-danger">*</span></label>
                                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" required
                                       value="{{ old('amount', $subscription->amount) }}">
                                @error('amount')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <label for="tax_rate" class="form-label">{{ __('tax') }} %</label>
                                <input type="number" id="tax_rate" name="tax_rate" class="form-control" step="0.01" min="0" max="100" required
                                       value="{{ old('tax_rate', $subscription->tax_rate) }}">
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <label for="currency" class="form-label">{{ __('currency') }}</label>
                                <input type="text" id="currency" name="currency" class="form-control" required minlength="3" maxlength="3"
                                       value="{{ old('currency', $subscription->currency) }}">
                                @error('currency')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <label for="interval" class="form-label">{{ __('billing_period') }}</label>
                                <select id="interval" name="interval" class="form-select">
                                    @foreach(\App\Models\Subscription::intervals() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('interval', $subscription->interval) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('description') }}</label>
                            <textarea id="description" name="description" class="form-control" rows="4" maxlength="5000">{{ old('description', $subscription->description) }}</textarea>
                            <div class="form-text">{{ __('subscription_description_hint') }}</div>
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
