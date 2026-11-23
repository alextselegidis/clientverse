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
    {{ __('expenses') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('expenses')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('expenses.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@php
    $sortUrl = fn ($column) => route('expenses', [
        'sort' => $column,
        'direction' => request('direction') === 'asc' ? 'desc' : 'asc',
        'q' => $q,
        'category' => $category,
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
                    <form action="{{ route('expenses') }}" method="GET" class="row g-3 flex-grow-1">
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
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('category') }}: {{ __('all') }}</option>
                                @foreach($categories as $option)
                                    <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @include('shared.bulk-actions', ['tableId' => 'expenses', 'route' => route('expenses.bulk-destroy')])
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0" id="expenses-table">
                    <thead>
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" data-table="expenses">
                        </th>
                        <th class="w-100 w-md-auto">
                            <a href="{{ $sortUrl('description') }}" class="text-decoration-none">{{ __('description') }}{!! $sortIcon('description') !!}</a>
                        </th>
                        <th class="d-none d-lg-table-cell">{{ __('category') }}</th>
                        <th class="d-none d-lg-table-cell">{{ __('customer') }}</th>
                        <th class="d-none d-md-table-cell text-nowrap">
                            <a href="{{ $sortUrl('expense_date') }}" class="text-decoration-none">{{ __('date') }}{!! $sortIcon('expense_date') !!}</a>
                        </th>
                        <th class="text-nowrap text-end">
                            <a href="{{ $sortUrl('amount') }}" class="text-decoration-none">{{ __('amount') }}{!! $sortIcon('amount') !!}</a>
                        </th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($expenses as $expense)
                        @php($showUrl = route('expenses.edit', $expense->id))
                        <tr>
                            <td class="ps-3" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input bulk-select-item" data-table="expenses" value="{{ $expense->id }}">
                            </td>
                            <td style="width: 32%;">
                                <a href="{{ $showUrl }}" class="fw-medium text-truncate d-block text-body text-decoration-none" style="width: 0; min-width: 100%;">{{ $expense->description }}</a>
                            </td>
                            <td class="d-none d-lg-table-cell">{{ $expense->category }}</td>
                            <td class="d-none d-lg-table-cell">
                                @if($expense->customer)
                                    <a href="{{ route('customers.show', $expense->customer_id) }}" onclick="event.stopPropagation()">{{ $expense->customer->name }}</a>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell">
                                {{ $expense->expense_date->format('M d, Y') }}
                            </td>
                            <td class="text-end">
                                {{ format_currency($expense->amount, $expense->currency) }}
                                @if($expense->invoice_id)
                                    <i class="bi bi-receipt text-success ms-1" title="{{ __('billed') }}"></i>
                                @elseif($expense->billable)
                                    <i class="bi bi-receipt text-muted ms-1" title="{{ __('billable') }}"></i>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"
                                            aria-label="{{ __('actions') }}" title="{{ __('actions') }}">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('expenses.edit', $expense->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('expenses.destroy', $expense->id) }}"
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
                    @if($expenses->isEmpty())
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
            @include('shared.pagination', ['paginator' => $expenses])
        </div>
    </div>

    @include('shared.bulk-actions-script')
@endsection
