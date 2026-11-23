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
    {{ $creditNote->number }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('credit_notes'), 'url' => route('credit-notes')],
        ['label' => $creditNote->number]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('credit-notes.print', $creditNote->id) }}" class="nav-link me-lg-3" target="_blank">
        <i class="bi bi-printer me-2"></i>
        {{ __('print') }}
    </a>
    <a href="{{ route('credit-notes.edit', $creditNote->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('credit-notes.destroy', $creditNote->id) }}"
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
                    <h5 class="mb-0">{{ __('details') }}</h5>
                    <span class="badge bg-{{ \App\Models\CreditNote::statusColors()[$creditNote->status] }} ms-2">
                        {{ __($creditNote->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($creditNote->customer)
                                    <a href="{{ route('customers.show', $creditNote->customer_id) }}">{{ $creditNote->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2"><strong>{{ __('issue_date') }}:</strong> {{ $creditNote->issue_date?->format('M d, Y') ?? '-' }}</p>
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
                            @forelse($creditNote->items as $item)
                                <tr>
                                    <td><div class="text-truncate" style="width: 0; min-width: 100%;">{{ $item->description }}</div></td>
                                    <td class="text-end">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ format_currency($item->unit_price, $creditNote->currency) }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%</td>
                                    <td class="text-end">{{ format_currency($item->subtotal, $creditNote->currency) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('no_records_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @include('shared.document-totals', ['document' => $creditNote, 'class' => 'mt-3 mb-0'])

                    @if($creditNote->notes)
                        <hr>
                        <h6>{{ __('notes') }}</h6>
                        <p class="text-muted">{!! nl2br(e($creditNote->notes)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $creditNote->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $creditNote->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0">{{ __('invoice') }}</h5></div>
                <div class="card-body">
                    @if($creditNote->invoice)
                        <p>
                            <a href="{{ route('invoices.show', $creditNote->invoice_id) }}">{{ $creditNote->invoice->number }}</a>
                            @if($creditNote->status === 'draft')
                                <span class="text-muted small d-block">{{ __('credit_note_draft_hint') }}</span>
                            @endif
                        </p>
                        <form action="{{ route('credit-notes.unapply', $creditNote->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light border w-100">{{ __('remove_from_invoice') }}</button>
                        </form>
                    @elseif($openInvoices->isEmpty())
                        <p class="text-muted mb-0">{{ __('no_open_invoices_message') }}</p>
                    @else
                        <form action="{{ route('credit-notes.apply', $creditNote->id) }}" method="POST">
                            @csrf
                            <select name="invoice_id" class="form-select mb-3" aria-label="{{ __('invoice') }}" required>
                                @foreach($openInvoices as $invoice)
                                    <option value="{{ $invoice->id }}">
                                        {{ $invoice->number }} · {{ format_currency($invoice->balance, $invoice->currency) }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary w-100">{{ __('apply_to_invoice') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
