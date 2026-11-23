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
{{-- Customer-facing subscription page, reached through Subscription::publicUrl(). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
    <meta name="robots" content="noindex">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 640px;">
    <div class="bg-white shadow-sm rounded-3 p-4 p-md-5">
        <div class="text-muted mb-1">{{ setting('company_name') ?: config('app.name') }}</div>
        <h1 class="h3 mb-3">{{ $subscription->name }}</h1>

        <div class="d-flex align-items-baseline gap-2 mb-4">
            <span class="fs-2 fw-bold">{{ format_currency($subscription->gross_amount, $subscription->currency) }}</span>
            <span class="text-muted">/ {{ __('per_' . $subscription->interval) }}</span>
        </div>
        @if((float) $subscription->tax_rate > 0)
            <p class="small text-muted mt-n3 mb-4">
                {{ __('including_tax', ['rate' => rtrim(rtrim($subscription->tax_rate, '0'), '.')]) }}
            </p>
        @endif

        @if($subscription->description)
            <div class="mb-4">{!! nl2br(e($subscription->description)) !!}</div>
        @endif

        <hr>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(request('subscribed') || in_array($subscription->status, ['active', 'past_due'], true))
            <p class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>{{ __('subscription_active_message') }}</p>
        @elseif($subscription->canBeSubscribedTo())
            <form action="{{ URL::signedRoute('public.subscriptions.subscribe', ['subscription' => $subscription->id]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-credit-card me-2"></i>{{ __('subscribe') }}
                </button>
            </form>
        @else
            <p class="text-muted mb-0">{{ __('subscription_unavailable_message') }}</p>
        @endif
    </div>
</div>
</body>
</html>
