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
    {{ __('contracts') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('contracts')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('contracts.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search and Filters -->
            <form action="{{ route('contracts') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="q" name="q" class="form-control bg-light border-start-0"
                               value="{{ $q }}"
                               placeholder="{{ __('search') }}...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('type') }}: {{ __('all') }}</option>
                        @foreach(\App\Models\Contract::types() as $key => $label)
                            <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('status') }}: {{ __('all') }}</option>
                        @foreach(\App\Models\Contract::statuses() as $key => $label)
                            <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                            <a href="{{ route('contracts', ['sort' => 'title', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('title') }}
                                @if(request('sort') === 'title')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('contracts', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('customer') }}
                                @if(request('sort') === 'customer')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('contracts', ['sort' => 'type', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('type') }}
                                @if(request('sort') === 'type')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('contracts', ['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('status') }}
                                @if(request('sort') === 'status')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('contracts', ['sort' => 'value', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('value') }}
                                @if(request('sort') === 'value')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('contracts', ['sort' => 'start_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'type' => $type, 'status' => $status]) }}" class="text-decoration-none text-white">
                                {{ __('dates') }}
                                @if(request('sort') === 'start_date')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0 pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contracts as $contract)
                        <tr onclick="window.location='{{ route('contracts.show', $contract->id) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <span class="fw-medium">{{ $contract->title }}</span>
                            </td>
                            <td>
                                @if($contract->customer)
                                    <a href="{{ route('customers.show', $contract->customer_id) }}" onclick="event.stopPropagation()">
                                        {{ $contract->customer->name }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $contract->type == 'recurring' ? 'info' : 'secondary' }}">
                                    {{ __($contract->type) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusColors = ['draft' => 'secondary', 'active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$contract->status] ?? 'secondary' }}">
                                    {{ __($contract->status) }}
                                </span>
                            </td>
                            <td>
                                @if($contract->value)
                                    {{ $contract->customer?->currency ?? 'USD' }} {{ number_format($contract->value, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ $contract->start_date?->format('M d, Y') ?? '-' }}
                                    @if($contract->end_date)
                                        <br>→ {{ $contract->end_date->format('M d, Y') }}
                                    @endif
                                </small>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('contracts.edit', $contract->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('contracts.destroy', $contract->id) }}"
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
                    @if($contracts->isEmpty())
                        <tr>
                            <td colspan="7" class="border-0 text-center text-muted py-5">
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
