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
    {{ $contract->title }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('contracts'), 'url' => route('contracts')],
        ['label' => $contract->title]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('contracts.create') }}" class="nav-link me-lg-3">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
    <a href="{{ route('contracts.edit', $contract->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
    </a>
    <form action="{{ route('contracts.destroy', $contract->id) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>{{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1">{{ $contract->title }}</h4>
                            @if($contract->customer)
                                <a href="{{ route('customers.show', $contract->customer_id) }}" class="text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $contract->customer->name }}
                                </a>
                            @endif
                        </div>
                        <div class="text-end">
                            @php
                                $statusColors = ['draft' => 'secondary', 'active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$contract->status] ?? 'secondary' }} fs-6">
                                {{ __($contract->status) }}
                            </span>
                            <span class="badge bg-{{ $contract->type == 'recurring' ? 'info' : 'secondary' }} fs-6 ms-1">
                                {{ __($contract->type) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('value') }}</small>
                            <span class="fs-5 fw-bold">
                                @if($contract->value)
                                    {{ format_currency($contract->value, $contract->customer?->currency) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('start_date') }}</small>
                            <span>{{ $contract->start_date?->format('M d, Y') ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('end_date') }}</small>
                            <span>{{ $contract->end_date?->format('M d, Y') ?? '-' }}</span>
                        </div>
                    </div>

                    @if($contract->description)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('description') }}</h6>
                            <p class="mb-0">{!! nl2br(e($contract->description)) !!}</p>
                        </div>
                    @endif

                    @if($contract->notes)
                        <div>
                            <h6 class="text-muted mb-2">{{ __('notes') }}</h6>
                            <p class="mb-0">{!! nl2br(e($contract->notes)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            @include('shared.files-section', [
                'model' => $contract,
                'uploadRoute' => route('contracts.files.upload', $contract->id),
                'downloadRoute' => 'contracts.files.download',
                'deleteRoute' => 'contracts.files.delete',
                'routeParams' => ['contract' => $contract->id],
                'uploadLimits' => $uploadLimits,
            ])
        </div>

        <div class="col-lg-4">
            <!-- Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">{{ __('details') }}</h6>
                </div>
                <div class="card-body">
                    @if($contract->project)
                        <small class="text-muted">{{ __('project') }}</small>
                        <p class="mb-3">
                            <a href="{{ route('projects.show', $contract->project_id) }}">
                                {{ $contract->project->name }}
                            </a>
                        </p>
                    @endif

                    @if($contract->sale)
                        <small class="text-muted">{{ __('sale') }}</small>
                        <p class="mb-3">
                            <a href="{{ route('sales.show', $contract->sale_id) }}">
                                {{ $contract->sale->title }}
                            </a>
                        </p>
                    @endif

                    <small class="text-muted">{{ __('created') }}</small>
                    <p class="mb-3">{{ $contract->created_at->format('M d, Y H:i') }}</p>

                    <small class="text-muted">{{ __('updated') }}</small>
                    <p class="mb-0">{{ $contract->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
