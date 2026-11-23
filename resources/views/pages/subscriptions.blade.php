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
    {{ __('subscriptions') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('subscriptions')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('subscriptions.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@php
    $sortUrl = fn ($column) => route('subscriptions', [
        'sort' => $column,
        'direction' => request('direction') === 'asc' ? 'desc' : 'asc',
        'q' => $q,
        'status' => $status,
    ]);
    $sortIcon = fn ($column) => request('sort') === $column
        ? '<i class="bi bi-chevron-' . (request('direction') === 'asc' ? 'up' : 'down') . ' ms-1"></i>'
        : '';
@endphp

@section('content')
    <div class="card card-table border-0 shadow-sm">
        <div class="card-body">
            <div class="table-filters">
                <form action="{{ route('subscriptions') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="q" name="q" class="form-control border-start-0"
                                   value="{{ $q }}" placeholder="{{ __('search') }}...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select" onchange="this.form.submit()" aria-label="{{ __('status') }}">
                            <option value="">{{ __('status') }}: {{ __('all') }}</option>
                            @foreach(\App\Models\Subscription::statuses() as $key => $label)
                                <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-3 w-100 w-md-auto">
                            <a href="{{ $sortUrl('name') }}" class="text-decoration-none">{{ __('name') }}{!! $sortIcon('name') !!}</a>
                        </th>
                        <th class="d-none d-md-table-cell text-nowrap">
                            <a href="{{ $sortUrl('amount') }}" class="text-decoration-none">{{ __('price') }}{!! $sortIcon('amount') !!}</a>
                        </th>
                        <th class="d-none d-lg-table-cell">{{ __('customer') }}</th>
                        <th class="d-none d-lg-table-cell text-nowrap">{{ __('renews') }}</th>
                        <th>
                            <a href="{{ $sortUrl('status') }}" class="text-decoration-none">{{ __('status') }}{!! $sortIcon('status') !!}</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($subscriptions as $subscription)
                        @php($showUrl = route('subscriptions.show', $subscription->id))
                        <tr>
                            <td class="ps-3" style="width: 32%;">
                                <a href="{{ $showUrl }}" class="fw-medium text-truncate d-block text-body text-decoration-none" style="width: 0; min-width: 100%;">{{ $subscription->name }}</a>
                            </td>
                            <td class="d-none d-md-table-cell text-nowrap">
                                {{ format_currency($subscription->gross_amount, $subscription->currency) }}
                                <span class="text-muted">/ {{ __('per_' . $subscription->interval) }}</span>
                            </td>
                            <td class="d-none d-lg-table-cell" style="width: 22%;">
                                @if($subscription->customer)
                                    <div class="text-truncate" style="width: 0; min-width: 100%;">
                                        <a href="{{ route('customers.show', $subscription->customer_id) }}" onclick="event.stopPropagation()">{{ $subscription->customer->name }}</a>
                                    </div>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $subscription->current_period_end?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ \App\Models\Subscription::statusColors()[$subscription->status] }}">
                                    {{ \App\Models\Subscription::statuses()[$subscription->status] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    @if($subscriptions->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            @include('shared.pagination', ['paginator' => $subscriptions])
        </div>
    </div>
@endsection
