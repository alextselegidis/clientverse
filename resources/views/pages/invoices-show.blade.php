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
    {{ $invoice->number }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('invoices'), 'url' => route('invoices')],
        ['label' => $invoice->number]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('invoices.edit', $invoice->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('invoices.destroy', $invoice->id) }}"
          method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <h5 class="mb-0">{{ $invoice->number }}</h5>
                    <span class="badge bg-{{ \App\Models\Invoice::statusColors()[$invoice->display_status] }} ms-2">
                        {{ __($invoice->display_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($invoice->customer)
                                    <a href="{{ route('customers.show', $invoice->customer_id) }}">{{ $invoice->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2"><strong>{{ __('issue_date') }}:</strong> {{ $invoice->issue_date?->format('M d, Y') ?? '-' }}</p>
                            <p class="mb-2"><strong>{{ __('due_date') }}:</strong> {{ $invoice->due_date?->format('M d, Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-light-header align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="w-100">{{ __('description') }}</th>
                                <th class="text-end">{{ __('quantity') }}</th>
                                <th class="text-end d-none d-md-table-cell">{{ __('unit_price') }}</th>
                                <th class="text-end d-none d-md-table-cell">{{ __('tax') }}</th>
                                <th class="text-end">{{ __('amount') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td><div class="text-truncate" style="width: 0; min-width: 100%;">{{ $item->description }}</div></td>
                                    <td class="text-end">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ format_currency($item->unit_price, $invoice->currency) }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%</td>
                                    <td class="text-end">{{ format_currency($item->subtotal, $invoice->currency) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('no_records_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <dl class="row mt-3 mb-0 ms-auto" style="max-width: 320px;">
                        <dt class="col-6 fw-normal text-muted">{{ __('subtotal') }}</dt>
                        <dd class="col-6 text-end mb-1">{{ format_currency($invoice->subtotal, $invoice->currency) }}</dd>
                        <dt class="col-6 fw-normal text-muted">{{ __('tax') }}</dt>
                        <dd class="col-6 text-end mb-1">{{ format_currency($invoice->tax, $invoice->currency) }}</dd>
                        <dt class="col-6">{{ __('total') }}</dt>
                        <dd class="col-6 text-end fw-bold mb-1">{{ format_currency($invoice->total, $invoice->currency) }}</dd>
                        <dt class="col-6 fw-normal text-muted">{{ __('amount_paid') }}</dt>
                        <dd class="col-6 text-end mb-1">{{ format_currency($invoice->amount_paid, $invoice->currency) }}</dd>
                        <dt class="col-6">{{ __('balance_due') }}</dt>
                        <dd class="col-6 text-end fw-bold mb-0">{{ format_currency($invoice->balance, $invoice->currency) }}</dd>
                    </dl>

                    @if($invoice->notes)
                        <hr>
                        <h6>{{ __('notes') }}</h6>
                        <p class="text-muted">{!! nl2br(e($invoice->notes)) !!}</p>
                    @endif
                    @if($invoice->terms)
                        <h6>{{ __('terms') }}</h6>
                        <p class="text-muted mb-0">{!! nl2br(e($invoice->terms)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $invoice->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $invoice->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">{{ __('payments') }}</h5>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($invoice->payments as $payment)
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ format_currency($payment->amount, $invoice->currency) }}</div>
                                <div class="small text-muted text-truncate">
                                    {{ $payment->paid_on->format('M d, Y') }}
                                    @if($payment->method) · {{ $payment->method }} @endif
                                    @if($payment->reference) · {{ $payment->reference }} @endif
                                </div>
                            </div>
                            <form action="{{ route('invoices.payments.destroy', [$invoice->id, $payment->id]) }}" method="POST"
                                  onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon" aria-label="{{ __('delete') }}" title="{{ __('delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('no_records_found') }}</li>
                    @endforelse
                </ul>
                @if($invoice->status !== 'cancelled')
                    <div class="card-body border-top">
                        <form action="{{ route('invoices.payments.store', $invoice->id) }}" method="POST" id="payment-form">
                            @csrf
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="amount" class="form-label">{{ __('amount') }}</label>
                                    <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" required
                                           value="{{ old('amount', $invoice->balance > 0 ? number_format($invoice->balance, 2, '.', '') : '') }}">
                                </div>
                                <div class="col-6">
                                    <label for="paid_on" class="form-label">{{ __('date') }}</label>
                                    <input type="date" id="paid_on" name="paid_on" class="form-control" required
                                           value="{{ old('paid_on', today()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-6">
                                    <label for="method" class="form-label">{{ __('method') }}</label>
                                    <input type="text" id="method" name="method" class="form-control" maxlength="100"
                                           value="{{ old('method') }}" placeholder="{{ __('bank_transfer') }}">
                                </div>
                                <div class="col-6">
                                    <label for="reference" class="form-label">{{ __('reference') }}</label>
                                    <input type="text" id="reference" name="reference" class="form-control" maxlength="255"
                                           value="{{ old('reference') }}">
                                </div>
                            </div>
                            @include('shared.errors', ['class' => 'mt-3 mb-0'])
                            <button type="submit" class="btn btn-primary w-100 mt-3">{{ __('record_payment') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
