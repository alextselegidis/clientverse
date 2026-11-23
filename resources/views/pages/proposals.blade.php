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
    {{ __('proposals') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('proposals')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('proposals.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@php
    $sortUrl = fn ($column) => route('proposals', [
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
            <!-- Search and Filters -->
            <div class="table-filters">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <form action="{{ route('proposals') }}" method="GET" class="row g-3 flex-grow-1">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="q" name="q" class="form-control border-start-0"
                                       value="{{ $q }}"
                                       placeholder="{{ __('search') }}...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('status') }}: {{ __('all') }}</option>
                                @foreach(\App\Models\Proposal::statuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @include('shared.bulk-actions', ['tableId' => 'proposals', 'route' => route('proposals.bulk-destroy')])
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0" id="proposals-table">
                    <thead>
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" data-table="proposals">
                        </th>
                        <th class="w-100 w-md-auto">
                            <a href="{{ $sortUrl('number') }}" class="text-decoration-none">{{ __('number') }}{!! $sortIcon('number') !!}</a>
                        </th>
                        <th class="d-none d-lg-table-cell">{{ __('customer') }}</th>
                        <th class="d-none d-md-table-cell text-nowrap">
                            <a href="{{ $sortUrl('total') }}" class="text-decoration-none">{{ __('total') }}{!! $sortIcon('total') !!}</a>
                        </th>
                        <th class="d-none d-lg-table-cell text-nowrap">
                            <a href="{{ $sortUrl('open_till') }}" class="text-decoration-none">{{ __('open_till') }}{!! $sortIcon('open_till') !!}</a>
                        </th>
                        <th>{{ __('status') }}</th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($proposals as $proposal)
                        @php($showUrl = route('proposals.show', $proposal->id))
                        <tr>
                            <td class="ps-3" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input bulk-select-item" data-table="proposals" value="{{ $proposal->id }}">
                            </td>
                            <td onclick="window.location='{{ $showUrl }}'" style="cursor: pointer; width: 32%;">
                                <div class="text-truncate" style="width: 0; min-width: 100%;">
                                    <span class="fw-medium">{{ $proposal->number }}</span>
                                    <span class="text-muted ms-2">{{ $proposal->subject }}</span>
                                </div>
                            </td>
                            <td class="d-none d-lg-table-cell" onclick="window.location='{{ $showUrl }}'" style="cursor: pointer;">
                                @if($proposal->customer)
                                    <a href="{{ route('customers.show', $proposal->customer_id) }}" onclick="event.stopPropagation()">
                                        {{ $proposal->customer->name }}
                                    </a>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell text-nowrap" onclick="window.location='{{ $showUrl }}'" style="cursor: pointer;">
                                {{ format_currency($proposal->total, $proposal->currency) }}
                            </td>
                            <td class="d-none d-lg-table-cell" onclick="window.location='{{ $showUrl }}'" style="cursor: pointer;">
                                {{ $proposal->open_till?->format('M d, Y') }}
                            </td>
                            <td onclick="window.location='{{ $showUrl }}'" style="cursor: pointer;">
                                <span class="badge bg-{{ \App\Models\Proposal::statusColors()[$proposal->status] }}">
                                    {{ __($proposal->status) }}
                                </span>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"
                                            aria-label="{{ __('actions') }}" title="{{ __('actions') }}">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('proposals.edit', $proposal->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('proposals.destroy', $proposal->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>{{ __('delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($proposals->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @include('shared.pagination', ['paginator' => $proposals])
        </div>
    </div>

    @include('shared.bulk-actions-script')
@endsection
