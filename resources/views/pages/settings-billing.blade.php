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

                        <h6 class="mt-3 mb-1">{{ __('email_templates') }}</h6>
                        <p class="form-text mt-0 mb-3">{{ __('email_templates_hint') }}</p>

                        @foreach(['invoice', 'estimate', 'contract'] as $type)
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
