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
    {{ __('reports') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('reports')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('reports.payments-csv', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" class="nav-link">
        <i class="bi bi-download me-2"></i>
        {{ __('export_payments') }}
    </a>
@endsection

@section('content')
    <form action="{{ route('reports') }}" method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-6 col-md-3">
            <label for="from" class="form-label">{{ __('from') }}</label>
            <input type="date" id="from" name="from" class="form-control" value="{{ $from->toDateString() }}">
        </div>
        <div class="col-6 col-md-3">
            <label for="to" class="form-label">{{ __('to') }}</label>
            <input type="date" id="to" name="to" class="form-control" value="{{ $to->toDateString() }}">
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-light border w-100">{{ __('apply') }}</button>
        </div>
        @include('shared.errors', ['class' => 'col-12 mb-0'])
    </form>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0">{{ __('invoiced_and_collected') }}</h5></div>
                <div class="table-responsive">
                    <table class="table table-light-header align-middle mb-0">
                        <thead>
                        <tr>
                            <th class="ps-3">{{ __('month') }}</th>
                            <th class="d-none d-md-table-cell">{{ __('currency') }}</th>
                            <th class="text-end">{{ __('invoiced') }}</th>
                            <th class="text-end pe-3">{{ __('collected') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($monthly as $month => $currencies)
                            @foreach($currencies as $currency => $row)
                                <tr>
                                    <td class="ps-3">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</td>
                                    <td class="d-none d-md-table-cell">{{ $currency }}</td>
                                    <td class="text-end">{{ format_currency($row['invoiced'] ?? 0, $currency) }}</td>
                                    <td class="text-end pe-3">{{ format_currency($row['collected'] ?? 0, $currency) }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">{{ __('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent"><h5 class="mb-0">{{ __('outstanding_by_customer') }}</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse($outstanding as $row)
                        <li class="list-group-item d-flex justify-content-between gap-3">
                            <span class="text-truncate">
                                @if($row['customer'])
                                    <a href="{{ route('customers.show', $row['customer']->id) }}">{{ $row['customer']->name }}</a>
                                @else
                                    -
                                @endif
                                <span class="text-muted small ms-1">{{ trans_choice('invoice_count', $row['count']) }}</span>
                            </span>
                            <span class="fw-medium text-nowrap">{{ format_currency($row['balance'], $row['currency']) }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('no_records_found') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h5 class="mb-0">{{ __('time_logged') }}</h5></div>
                @php($maxMinutes = max(1, $time->max('minutes') ?? 1))
                <ul class="list-group list-group-flush">
                    @forelse($time as $row)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-truncate">{{ $row['user']?->name ?? '-' }}</span>
                                <span class="fw-medium">{{ format_minutes($row['minutes']) }}</span>
                            </div>
                            <div class="progress mt-1" style="height: 4px;" role="presentation">
                                <div class="progress-bar" style="width: {{ round($row['minutes'] / $maxMinutes * 100) }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('no_records_found') }}</li>
                    @endforelse
                </ul>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0">{{ __('tickets') }}</h5></div>
                <div class="card-body row text-center g-0">
                    <div class="col-4"><div class="h4 mb-0">{{ $tickets['opened'] }}</div><div class="small text-muted">{{ __('opened') }}</div></div>
                    <div class="col-4"><div class="h4 mb-0">{{ $tickets['closed'] }}</div><div class="small text-muted">{{ __('closed') }}</div></div>
                    <div class="col-4"><div class="h4 mb-0">{{ $tickets['open_now'] }}</div><div class="small text-muted">{{ __('open_now') }}</div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
