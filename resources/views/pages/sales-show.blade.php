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
    {{ $sale->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('sales'), 'url' => route('sales')],
        ['label' => $sale->name]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('sales.create') }}" class="nav-link me-lg-3">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
    <a href="{{ route('sales.edit', $sale->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    @if($sale->stage !== 'won' && $sale->stage !== 'lost')
        <form action="{{ route('sales.convert-to-contract', $sale->id) }}" method="POST" class="d-inline me-lg-3">
            @csrf
            <button type="submit" class="nav-link">
                <i class="bi bi-file-earmark-text me-2"></i>
                {{ __('convert_to_contract') }}
            </button>
        </form>
    @endif
    <form action="{{ route('sales.destroy', $sale->id) }}"
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
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center">
                    <h5 class="mb-0">{{ $sale->name }}</h5>
                    @php
                        $stageColors = ['lead' => 'secondary', 'qualified' => 'info', 'proposal_sent' => 'warning', 'won' => 'success', 'lost' => 'danger'];
                    @endphp
                    <span class="badge bg-{{ $stageColors[$sale->stage] ?? 'secondary' }} ms-2">
                        {{ __($sale->stage) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($sale->customer)
                                    <a href="{{ route('customers.show', $sale->customer_id) }}">{{ $sale->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('value') }}:</strong>
                                @if($sale->value)
                                    {{ format_currency($sale->value, $sale->currency) }}
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('probability') }}:</strong>
                                {{ $sale->probability }}%
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('expected_value') }}:</strong>
                                @if($sale->value && $sale->probability)
                                    {{ format_currency($sale->expected_value, $sale->currency) }}
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('due_date') }}:</strong>
                                {{ $sale->expected_close_date?->format('M d, Y') ?? '-' }}
                            </p>
                        </div>
                    </div>
                    @if($sale->notes)
                        <hr>
                        <h6>{{ __('notes') }}</h6>
                        <p class="text-muted mb-0">{!! nl2br(e($sale->notes)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $sale->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $sale->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>
@endsection
