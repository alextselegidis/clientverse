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
    {{ $proposal->number }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('proposals'), 'url' => route('proposals')],
        ['label' => $proposal->number]
    ]])
@endsection

@section('navActions')
    @if($proposal->estimate)
        <a href="{{ route('estimates.show', $proposal->estimate_id) }}" class="nav-link me-lg-3">
            <i class="bi bi-file-earmark-ruled me-2"></i>
            {{ $proposal->estimate->number }}
        </a>
    @else
        <form action="{{ route('proposals.convert-to-estimate', $proposal->id) }}" method="POST" class="d-inline me-lg-3">
            @csrf
            <button type="submit" class="nav-link">
                <i class="bi bi-file-earmark-ruled me-2"></i>
                {{ __('convert_to_estimate') }}
            </button>
        </form>
    @endif
    <form action="{{ route('proposals.email', $proposal->id) }}" method="POST" class="d-inline me-lg-3">
        @csrf
        <button type="submit" class="nav-link">
            <i class="bi bi-envelope me-2"></i>
            {{ __('send_by_email') }}
        </button>
    </form>
    <a href="{{ route('proposals.print', $proposal->id) }}" class="nav-link me-lg-3" target="_blank">
        <i class="bi bi-printer me-2"></i>
        {{ __('print') }}
    </a>
    <a href="{{ route('proposals.edit', $proposal->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('proposals.destroy', $proposal->id) }}"
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
                    <h5 class="mb-0 text-truncate">{{ $proposal->number }} · {{ $proposal->subject }}</h5>
                    <span class="badge bg-{{ \App\Models\Proposal::statusColors()[$proposal->status] }} ms-2">
                        {{ __($proposal->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($proposal->customer)
                                    <a href="{{ route('customers.show', $proposal->customer_id) }}">{{ $proposal->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            @if($proposal->sale)
                                <p class="mb-2"><strong>{{ __('sale') }}:</strong>
                                    <a href="{{ route('sales.show', $proposal->sale_id) }}">{{ $proposal->sale->name }}</a></p>
                            @endif
                            <p class="mb-2"><strong>{{ __('issue_date') }}:</strong> {{ $proposal->issue_date?->format('M d, Y') ?? '-' }}</p>
                            <p class="mb-2"><strong>{{ __('open_till') }}:</strong> {{ $proposal->open_till?->format('M d, Y') ?? '-' }}</p>
                        </div>
                    </div>

                    @if($proposal->content)
                        <div class="mb-4">{!! nl2br(e($proposal->content)) !!}</div>
                    @endif

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
                            @forelse($proposal->items as $item)
                                <tr>
                                    <td><div class="text-truncate" style="width: 0; min-width: 100%;">{{ $item->description }}</div></td>
                                    <td class="text-end">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ format_currency($item->unit_price, $proposal->currency) }}</td>
                                    <td class="text-end d-none d-md-table-cell">{{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%</td>
                                    <td class="text-end">{{ format_currency($item->subtotal, $proposal->currency) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">{{ __('no_records_found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @include('shared.document-totals', ['document' => $proposal, 'class' => 'mt-3 mb-0'])

                    @if($proposal->notes)
                        <hr>
                        <h6>{{ __('notes') }}</h6>
                        <p class="text-muted">{!! nl2br(e($proposal->notes)) !!}</p>
                    @endif
                    @if($proposal->terms)
                        <h6>{{ __('terms') }}</h6>
                        <p class="text-muted mb-0">{!! nl2br(e($proposal->terms)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $proposal->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $proposal->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($proposal->status === 'draft')
                <p class="text-muted small">{{ __('customer_link_draft_hint') }}</p>
            @else
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="public-url" class="form-label small text-muted">{{ __('customer_link_hint') }}</label>
                    <input type="text" id="public-url" class="form-control form-control-sm" readonly
                           value="{{ $proposal->publicUrl() }}" onclick="this.select()">
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
