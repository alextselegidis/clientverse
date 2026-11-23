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
    {{ $subscription->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('subscriptions'), 'url' => route('subscriptions')],
        ['label' => $subscription->name]
    ]])
@endsection

@section('navActions')
    @if($subscription->isEditable())
        <form action="{{ route('subscriptions.email', $subscription->id) }}" method="POST" class="d-inline me-lg-3">
            @csrf
            <button type="submit" class="nav-link">
                <i class="bi bi-envelope me-2"></i>{{ __('send_by_email') }}
            </button>
        </form>
        <a href="{{ route('subscriptions.edit', $subscription->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
        </a>
    @endif
    @if($subscription->status !== 'canceled')
        <form action="{{ route('subscriptions.cancel', $subscription->id) }}" method="POST" class="d-inline me-lg-3"
              onsubmit="return confirm('{{ __('cancel_subscription_prompt') }}')">
            @csrf
            <button type="submit" class="nav-link">
                <i class="bi bi-x-circle me-2"></i>{{ __('cancel_subscription') }}
            </button>
        </form>
    @endif
    @if($subscription->isEditable() || $subscription->status === 'canceled')
        <form action="{{ route('subscriptions.destroy', $subscription->id) }}" method="POST"
              onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="nav-link">
                <i class="bi bi-trash me-2"></i>{{ __('delete') }}
            </button>
        </form>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <h5 class="mb-0 text-truncate">{{ $subscription->name }}</h5>
                    <span class="badge bg-{{ \App\Models\Subscription::statusColors()[$subscription->status] }} ms-2">
                        {{ \App\Models\Subscription::statuses()[$subscription->status] }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('customer') }}</div>
                            @if($subscription->customer)
                                <a href="{{ route('customers.show', $subscription->customer_id) }}">{{ $subscription->customer->name }}</a>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('price') }}</div>
                            {{ format_currency($subscription->gross_amount, $subscription->currency) }} / {{ __('per_' . $subscription->interval) }}
                            @if((float) $subscription->tax_rate > 0)
                                <span class="text-muted small">({{ __('including_tax', ['rate' => rtrim(rtrim($subscription->tax_rate, '0'), '.')]) }})</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">{{ __('renews') }}</div>
                            {{ $subscription->current_period_end?->format('M d, Y') ?? '-' }}
                        </div>
                        @if($subscription->canceled_at)
                            <div class="col-md-6">
                                <div class="text-muted small">{{ __('cancelled') }}</div>
                                {{ $subscription->canceled_at->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                    @if($subscription->description)
                        <hr>
                        <p class="mb-0">{!! nl2br(e($subscription->description)) !!}</p>
                    @endif
                </div>
            </div>

            <div class="card card-table border-0 shadow-sm">
                <div class="card-header bg-transparent"><h2 class="h6 mb-0">{{ __('invoices') }}</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover table-light-header align-middle mb-0">
                        <tbody>
                        @forelse($subscription->invoices as $invoice)
                            <tr>
                                <td class="ps-3 w-100 w-md-auto"><a href="{{ route('invoices.show', $invoice->id) }}" class="fw-medium text-body text-decoration-none">{{ $invoice->number }}</a></td>
                                <td class="d-none d-md-table-cell">{{ $invoice->issue_date?->format('M d, Y') }}</td>
                                <td class="text-nowrap">{{ format_currency($invoice->total, $invoice->currency) }}</td>
                                <td class="pe-3">
                                    <span class="badge bg-{{ \App\Models\Invoice::statusColors()[$invoice->display_status] }}">{{ __($invoice->display_status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-4">{{ __('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6">{{ __('customer_link') }}</h2>
                    @if(blank(setting('stripe_secret_key')))
                        <p class="small text-muted mb-0">{{ __('subscription_needs_stripe_message') }}</p>
                    @elseif($subscription->isEditable())
                        <p class="small text-muted">{{ __('subscription_link_hint') }}</p>
                        <input type="text" class="form-control form-control-sm" readonly value="{{ $subscription->publicUrl() }}"
                               aria-label="{{ __('customer_link') }}" onclick="this.select()">
                    @else
                        <p class="small text-muted mb-0">{{ __('subscription_link_used_message') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
