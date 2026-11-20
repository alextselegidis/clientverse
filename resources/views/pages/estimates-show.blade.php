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
    {{ $estimate->number }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('estimates'), 'url' => route('estimates')],
        ['label' => $estimate->number]
    ]])
@endsection

@section('navActions')
    @if($estimate->invoice)
        <a href="{{ route('invoices.show', $estimate->invoice_id) }}" class="nav-link me-lg-3">
            <i class="bi bi-receipt me-2"></i>
            {{ $estimate->invoice->number }}
        </a>
    @else
        <form action="{{ route('estimates.convert-to-invoice', $estimate->id) }}" method="POST" class="d-inline me-lg-3">
            @csrf
            <button type="submit" class="nav-link">
                <i class="bi bi-receipt me-2"></i>
                {{ __('convert_to_invoice') }}
            </button>
        </form>
    @endif
    <form action="{{ route('estimates.email', $estimate->id) }}" method="POST" class="d-inline me-lg-3">
        @csrf
        <button type="submit" class="nav-link">
            <i class="bi bi-envelope me-2"></i>
            {{ __('send_by_email') }}
        </button>
    </form>
    <a href="{{ route('estimates.print', $estimate->id) }}" class="nav-link me-lg-3" target="_blank">
        <i class="bi bi-printer me-2"></i>
        {{ __('print') }}
    </a>
    <a href="{{ route('estimates.edit', $estimate->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('estimates.destroy', $estimate->id) }}"
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
                    <h5 class="mb-0">{{ $estimate->number }}</h5>
                    <span class="badge bg-{{ \App\Models\Estimate::statusColors()[$estimate->status] }} ms-2">
                        {{ __($estimate->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($estimate->customer)
                                    <a href="{{ route('customers.show', $estimate->customer_id) }}">{{ $estimate->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2"><strong>{{ __('issue_date') }}:</strong> {{ $estimate->issue_date?->format('M d, Y') ?? '-' }}</p>
                            <p class="mb-2"><strong>{{ __('expiry_date') }}:</strong> {{ $estimate->expiry_date?->format('M d, Y') ?? '-' }}</p>
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
                            @forelse($estimate->items as $item)
                                <tr>
                                    <td><div class="text-truncate" style="width: 0; min-width: 100%;">{{ $item->description }}</div></td>
                                    <td class="text-end">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ format_currency($item->unit_price, $estimate->currency) }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%</td>
                                    <td class="text-end">{{ format_currency($item->subtotal, $estimate->currency) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('no_records_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @include('shared.document-totals', ['document' => $estimate, 'class' => 'mt-3 mb-0'])

                    @if($estimate->notes)
                        <hr>
                        <h6>{{ __('notes') }}</h6>
                        <p class="text-muted">{!! nl2br(e($estimate->notes)) !!}</p>
                    @endif
                    @if($estimate->terms)
                        <h6>{{ __('terms') }}</h6>
                        <p class="text-muted mb-0">{!! nl2br(e($estimate->terms)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $estimate->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $estimate->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($estimate->status === 'draft')
                <p class="text-muted small">{{ __('customer_link_draft_hint') }}</p>
            @else
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="public-url" class="form-label small text-muted">{{ __('customer_link_hint') }}</label>
                    <input type="text" id="public-url" class="form-control form-control-sm" readonly
                           value="{{ $estimate->publicUrl() }}" onclick="this.select()">
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
