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
            {{-- One form, split into a card per topic so a setting is easy to find --}}
            <form action="{{ route('setup.billing.update') }}" method="POST" id="settings-form">
                        @csrf
                        @method('PUT')

                    <div class="card mb-4" id="company">
                        <div class="card-header py-3 px-4">
                            <h2 class="h6 mb-0">{{ __('company') }}</h2>
                        </div>
                        <div class="card-body p-4">
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

                        </div>
                    </div>

                    <div class="card mb-4" id="payments">
                        <div class="card-header py-3 px-4">
                            <h2 class="h6 mb-0">{{ __('online_payments') }}</h2>
                        </div>
                        <div class="card-body p-4">
                        <h3 class="h6 mb-1">Stripe</h3>
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

                        <hr class="my-4">
                        <h3 class="h6 mb-1">PayPal</h3>
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

                        </div>
                    </div>

                    <div class="card mb-4" id="reminders">
                        <div class="card-header py-3 px-4">
                            <h2 class="h6 mb-0">{{ __('reminders') }}</h2>
                        </div>
                        <div class="card-body p-4">
                        <input type="hidden" name="send_reminders" value="0">
                        <div class="form-check mb-1">
                            <input type="checkbox" id="send_reminders" name="send_reminders" value="1" class="form-check-input"
                                   {{ old('send_reminders', $values['send_reminders']) ? 'checked' : '' }}>
                            <label for="send_reminders" class="form-check-label">{{ __('send_reminders') }}</label>
                        </div>
                        <p class="form-text mt-0 mb-3">{{ __('send_reminders_hint') }}</p>
                        <div class="mb-3">
                            <label for="contract_reminder_days" class="form-label text-dark small fw-medium">{{ __('contract_reminder_days') }}</label>
                            <input type="number" id="contract_reminder_days" name="contract_reminder_days" class="form-control"
                                   min="1" max="365" placeholder="7" style="max-width: 10rem;"
                                   value="{{ old('contract_reminder_days', $values['contract_reminder_days']) }}">
                        </div>

                        {{-- Staff renewal notices are on until an admin switches them off --}}
                        <input type="hidden" name="staff_renewal_reminders" value="0">
                        <div class="form-check mb-1">
                            <input type="checkbox" id="staff_renewal_reminders" name="staff_renewal_reminders" value="1" class="form-check-input"
                                   {{ old('staff_renewal_reminders', $values['staff_renewal_reminders'] ?? '1') !== '0' ? 'checked' : '' }}>
                            <label for="staff_renewal_reminders" class="form-check-label">{{ __('staff_renewal_reminders') }}</label>
                        </div>
                        <div class="mb-3">
                            <label for="staff_renewal_days" class="form-label text-dark small fw-medium">{{ __('staff_renewal_days') }}</label>
                            <input type="text" id="staff_renewal_days" name="staff_renewal_days" class="form-control" maxlength="50"
                                   placeholder="30, 7" style="max-width: 10rem;"
                                   value="{{ old('staff_renewal_days', $values['staff_renewal_days']) }}">
                            <div class="form-text">{{ __('staff_renewal_days_hint') }}</div>
                        </div>

                        </div>
                    </div>

                    <div class="card mb-4" id="templates">
                        <div class="card-header py-3 px-4">
                            <h2 class="h6 mb-0">{{ __('email_templates') }}</h2>
                            <p class="form-text mt-1 mb-0">{{ __('email_templates_hint') }}</p>
                        </div>
                        <div class="card-body p-4">

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
                        </div>
                    </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
