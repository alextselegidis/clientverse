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
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search and Filters -->
            <form action="{{ route('sales') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="q" name="q" class="form-control bg-light border-start-0"
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

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="border-0 ps-3">
                            <a href="{{ route('sales', ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none text-white">
                                {{ __('name') }}
                                @if(request('sort') === 'name')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('sales', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none text-white">
                                {{ __('customer') }}
                                @if(request('sort') === 'customer')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('sales', ['sort' => 'value', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none text-white">
                                {{ __('value') }}
                                @if(request('sort') === 'value')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('sales', ['sort' => 'stage', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none text-white">
                                {{ __('stage') }}
                                @if(request('sort') === 'stage')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('sales', ['sort' => 'probability', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'stage' => $stage]) }}" class="text-decoration-none text-white">
                                {{ __('probability') }}
                                @if(request('sort') === 'probability')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0 pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sales as $sale)
                        <tr onclick="window.location='{{ route('sales.show', $sale->id) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <span class="fw-medium">{{ $sale->name }}</span>
                            </td>
                            <td>
                                @if($sale->customer)
                                    <a href="{{ route('customers.show', $sale->customer_id) }}" onclick="event.stopPropagation()">
                                        {{ $sale->customer->name }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                @if($sale->value)
                                    {{ $sale->currency ?? 'USD' }} {{ number_format($sale->value, 2) }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $stageColors = ['lead' => 'secondary', 'qualified' => 'info', 'proposal_sent' => 'warning', 'won' => 'success', 'lost' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $stageColors[$sale->stage] ?? 'secondary' }}">
                                    {{ __($sale->stage) }}
                                </span>
                            </td>
                            <td>{{ $sale->probability }}%</td>
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
                            <td colspan="6" class="border-0 text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
