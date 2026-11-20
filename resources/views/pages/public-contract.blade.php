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
{{-- Customer-facing contract page, reached through a signed link; see PublicContractsController. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
    <meta name="robots" content="noindex">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 860px;">
    <div class="bg-white shadow-sm rounded-3 p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $contract->title }}</h1>
                <div class="text-muted">{{ setting('company_name') ?: config('app.name') }}</div>
            </div>
            @if($contract->value)
                <div class="fw-bold text-nowrap">{{ format_currency($contract->value, $contract->currency) }}</div>
            @endif
        </div>

        <dl class="row small mb-4">
            @if($contract->customer)
                <dt class="col-sm-3 fw-normal text-muted">{{ __('customer') }}</dt>
                <dd class="col-sm-9">{{ $contract->customer->company ?: $contract->customer->name }}</dd>
            @endif
            <dt class="col-sm-3 fw-normal text-muted">{{ __('start_date') }}</dt>
            <dd class="col-sm-9">{{ $contract->start_date?->format('M d, Y') ?? '-' }}</dd>
            <dt class="col-sm-3 fw-normal text-muted">{{ __('end_date') }}</dt>
            <dd class="col-sm-9">{{ $contract->end_date?->format('M d, Y') ?? '-' }}</dd>
        </dl>

        @if($contract->description)
            <div class="mb-4">{!! nl2br(e($contract->description)) !!}</div>
        @endif

        <hr>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($contract->signed_at)
            <p class="mb-0">
                <i class="bi bi-check-circle text-success me-2"></i>
                {{ __('signed_by_on', ['name' => $contract->signer_name, 'date' => $contract->signed_at->format('M d, Y H:i')]) }}
            </p>
        @elseif(!$contract->canBeSigned())
            <p class="text-muted mb-0">{{ __('contract_not_signable_message') }}</p>
        @else
            <form action="{{ request()->fullUrl() }}" method="POST" style="max-width: 420px;">
                @csrf
                <label for="signer_name" class="form-label">{{ __('type_your_full_name') }}</label>
                <input type="text" id="signer_name" name="signer_name" class="form-control mb-2" required maxlength="255"
                       autocomplete="name" value="{{ old('signer_name') }}">
                <div class="form-check mb-3">
                    <input type="checkbox" id="agree" name="agree" value="1" class="form-check-input" required>
                    <label for="agree" class="form-check-label">{{ __('agree_to_sign') }}</label>
                </div>
                @include('shared.errors', ['class' => 'mb-3'])
                <button type="submit" class="btn btn-primary">{{ __('sign_contract') }}</button>
            </form>
        @endif
    </div>
</div>
</body>
</html>
