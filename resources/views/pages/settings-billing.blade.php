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
    {{ __('billing_settings') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup')],
        ['label' => __('billing_settings')]
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
                    <form action="{{ route('setup.billing.update') }}" method="POST" id="settings-form">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label text-dark small fw-medium">{{ __('company_name') }}</label>
                                <input type="text" id="company_name" name="company_name" class="form-control" maxlength="255"
                                       value="{{ old('company_name', $values['company_name']) }}" placeholder="{{ config('app.name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_address" class="form-label text-dark small fw-medium">{{ __('company_address') }}</label>
                                <textarea id="company_address" name="company_address" class="form-control" rows="3">{{ old('company_address', $values['company_address']) }}</textarea>
                            </div>
                        </div>

                        <h6 class="mt-3 mb-1">{{ __('online_payments') }}</h6>
                        <p class="form-text mt-0 mb-3">{{ __('stripe_hint', ['url' => url('api/stripe/webhook')]) }}</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="stripe_secret_key" class="form-label text-dark small fw-medium">{{ __('stripe_secret_key') }}</label>
                                <input type="password" id="stripe_secret_key" name="stripe_secret_key" class="form-control" maxlength="255"
                                       autocomplete="off" value="{{ old('stripe_secret_key', $values['stripe_secret_key']) }}" placeholder="sk_live_...">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stripe_webhook_secret" class="form-label text-dark small fw-medium">{{ __('stripe_webhook_secret') }}</label>
                                <input type="password" id="stripe_webhook_secret" name="stripe_webhook_secret" class="form-control" maxlength="255"
                                       autocomplete="off" value="{{ old('stripe_webhook_secret', $values['stripe_webhook_secret']) }}" placeholder="whsec_...">
                            </div>
                        </div>

                        <p class="form-text mt-0 mb-3">{{ __('paypal_hint') }}</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paypal_client_id" class="form-label text-dark small fw-medium">{{ __('paypal_client_id') }}</label>
                                <input type="text" id="paypal_client_id" name="paypal_client_id" class="form-control" maxlength="255"
                                       autocomplete="off" value="{{ old('paypal_client_id', $values['paypal_client_id']) }}">
                                @error('paypal_client_id')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="paypal_secret" class="form-label text-dark small fw-medium">{{ __('paypal_secret') }}</label>
                                <input type="password" id="paypal_secret" name="paypal_secret" class="form-control" maxlength="255"
                                       autocomplete="off" value="{{ old('paypal_secret', $values['paypal_secret']) }}">
                                @error('paypal_secret')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" name="paypal_sandbox" value="0">
                        <div class="form-check mb-3">
                            <input type="checkbox" id="paypal_sandbox" name="paypal_sandbox" value="1" class="form-check-input"
                                   @checked(old('paypal_sandbox', $values['paypal_sandbox']))>
                            <label for="paypal_sandbox" class="form-check-label">{{ __('paypal_sandbox') }}</label>
                        </div>

                        <h6 class="mt-3 mb-1">{{ __('reminders') }}</h6>
                        <input type="hidden" name="send_reminders" value="0">
                        <div class="form-check mb-1">
                            <input type="checkbox" id="send_reminders" name="send_reminders" value="1" class="form-check-input"
                                   {{ old('send_reminders', $values['send_reminders']) ? 'checked' : '' }}>
                            <label for="send_reminders" class="form-check-label">{{ __('send_reminders') }}</label>
                        </div>
                        <p class="form-text mt-0 mb-3">{{ __('send_reminders_hint') }}</p>

                        <h6 class="mt-3 mb-1">{{ __('email_templates') }}</h6>
                        <p class="form-text mt-0 mb-3">{{ __('email_templates_hint') }}</p>

                        @foreach(['invoice', 'estimate', 'proposal', 'contract', 'subscription', 'invoice_overdue', 'estimate_expiring', 'proposal_expiring', 'contract_expiring'] as $type)
                            <fieldset class="mb-4">
                                <legend class="fs-6 fw-medium">{{ __($type) }}</legend>
                                <div class="mb-2">
                                    <label for="{{ $type }}_email_subject" class="form-label small text-muted">{{ __('subject') }}</label>
                                    <input type="text" id="{{ $type }}_email_subject" name="{{ $type }}_email_subject" class="form-control" maxlength="255"
                                           value="{{ old($type . '_email_subject', $values[$type . '_email_subject']) }}"
                                           placeholder="{{ __('email_template_' . $type . '_subject') }}">
                                </div>
                                <label for="{{ $type }}_email_body" class="form-label small text-muted">{{ __('body') }}</label>
                                <textarea id="{{ $type }}_email_body" name="{{ $type }}_email_body" class="form-control" rows="6"
                                          placeholder="{{ __('email_template_' . $type . '_body') }}">{{ old($type . '_email_body', $values[$type . '_email_body']) }}</textarea>
                            </fieldset>
                        @endforeach

                        @include('shared.errors', ['class' => 'mb-0'])
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="settings-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
