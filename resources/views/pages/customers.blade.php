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
    {{ __('customers') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('customers.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <!-- Search and Filters -->
            <form action="{{ route('customers') }}" method="GET" class="row g-3 mb-4">
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
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('status') }}: {{ __('all') }}</option>
                        @foreach(\App\Models\Customer::statuses() as $key => $label)
                            <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('type') }}: {{ __('all') }}</option>
                        @foreach(\App\Models\Customer::types() as $key => $label)
                            <option value="{{ $key }}" {{ $type == $key ? 'selected' : '' }}>{{ $label }}</option>
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
                            <a href="{{ route('customers', ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status, 'type' => $type]) }}" class="text-decoration-none text-white">
                                {{ __('name') }}
                                @if(request('sort') === 'name')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('customers', ['sort' => 'company', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status, 'type' => $type]) }}" class="text-decoration-none text-white">
                                {{ __('company') }}
                                @if(request('sort') === 'company')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('customers', ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status, 'type' => $type]) }}" class="text-decoration-none text-white">
                                {{ __('email') }}
                                @if(request('sort') === 'email')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0">
                            <a href="{{ route('customers', ['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status, 'type' => $type]) }}" class="text-decoration-none text-white">
                                {{ __('status') }}
                                @if(request('sort') === 'status')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="border-0 pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customers as $customer)
                        <tr onclick="window.location='{{ route('customers.show', $customer->id) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <span class="fw-medium">{{ $customer->name }}</span>
                            </td>
                            <td>{{ $customer->company }}</td>
                            <td>
                                @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" onclick="event.stopPropagation()">{{ $customer->email }}</a>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = ['lead' => 'warning', 'active' => 'success', 'inactive' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$customer->status] ?? 'secondary' }}">
                                    {{ __($customer->status) }}
                                </span>
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('customers.edit', ['customer' => $customer->id]) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('customers.destroy', $customer->id) }}"
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
                    @if($customers->isEmpty())
                        <tr>
                            <td colspan="5" class="border-0 text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="mt-4">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
