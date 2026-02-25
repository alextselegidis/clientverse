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
    {{ __('sales') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('sales')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('sales.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card card-table border-0 shadow-sm">
        <div class="card-body">
            <!-- Search and Filters -->
            <div class="table-filters">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <form action="{{ route('sales') }}" method="GET" class="row g-3 flex-grow-1">
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
                            <select name="stage" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ __('stage') }}: {{ __('all') }}</option>
                                @foreach(\App\Models\Sale::stages() as $key => $label)
                                    <option value="{{ $key }}" {{ $stage == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @include('shared.bulk-actions', ['tableId' => 'sales', 'route' => route('sales.bulk-destroy')])
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-light-header align-middle mb-0" id="sales-table">
                    <thead>
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" data-table="sales">
                        </th>
                        <th>
                            <a href="{{ route('sales', ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none">
                                {{ __('name') }}
                                @if(request('sort') === 'name')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('sales', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none">
                                {{ __('customer') }}
                                @if(request('sort') === 'customer')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('sales', ['sort' => 'value', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none">
                                {{ __('value') }}
                                @if(request('sort') === 'value')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('sales', ['sort' => 'stage', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none">
                                {{ __('stage') }}
                                @if(request('sort') === 'stage')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('sales', ['sort' => 'probability', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none">
                                {{ __('probability') }}
                                @if(request('sort') === 'probability')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td class="ps-3" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input bulk-select-item" data-table="sales" value="{{ $sale->id }}">
                            </td>
                            <td onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">
                                <span class="fw-medium">{{ $sale->name }}</span>
                            </td>
                            <td onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">
                                @if($sale->customer)
                                    <a href="{{ route('customers.show', $sale->customer_id) }}" onclick="event.stopPropagation()">
                                        {{ $sale->customer->name }}
                                    </a>
                                @endif
                            </td>
                            <td onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">
                                @if($sale->value)
                                    {{ format_currency($sale->value, $sale->currency) }}
                                @endif
                            </td>
                            <td onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">
                                @php
                                    $stageColors = ['lead' => 'secondary', 'qualified' => 'info', 'proposal_sent' => 'warning', 'won' => 'success', 'lost' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $stageColors[$sale->stage] ?? 'secondary' }}">
                                    {{ __($sale->stage) }}
                                </span>
                            </td>
                            <td onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">{{ $sale->probability }}%</td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('sales.edit', $sale->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        @if($sale->stage !== 'won' && $sale->stage !== 'lost')
                                            <li>
                                                <form action="{{ route('sales.convert-to-contract', $sale->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-file-earmark-text me-2"></i>{{ __('convert_to_contract') }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('sales.convert-to-project', $sale->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-kanban me-2"></i>{{ __('convert_to_project') }}
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('sales.destroy', $sale->id) }}"
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
                    @if($sales->isEmpty())
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
            @include('shared.pagination', ['paginator' => $sales])
        </div>
    </div>

    @include('shared.bulk-actions-script')
@endsection
