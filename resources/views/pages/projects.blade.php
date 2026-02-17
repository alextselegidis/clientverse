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
    {{ __('projects') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('projects.create') }}" class="nav-link">
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
                    <form action="{{ route('projects') }}" method="GET" class="row g-3 flex-grow-1">
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
                                @foreach(\App\Models\Project::statuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    @include('shared.bulk-actions', ['tableId' => 'projects', 'route' => route('projects.bulk-destroy')])
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-light-header align-middle mb-0" id="projects-table">
                    <thead>
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" data-table="projects">
                        </th>
                        <th>
                            <a href="{{ route('projects', ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status]) }}" class="text-decoration-none">
                                {{ __('name') }}
                                @if(request('sort') === 'name')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('projects', ['sort' => 'customer', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status]) }}" class="text-decoration-none">
                                {{ __('customer') }}
                                @if(request('sort') === 'customer')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('projects', ['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status]) }}" class="text-decoration-none">
                                {{ __('status') }}
                                @if(request('sort') === 'status')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('projects', ['sort' => 'due_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q, 'status' => $status]) }}" class="text-decoration-none">
                                {{ __('due_date') }}
                                @if(request('sort') === 'due_date')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td class="ps-3" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input bulk-select-item" data-table="projects" value="{{ $project->id }}">
                            </td>
                            <td onclick="window.location='{{ route('projects.show', $project->id) }}'" style="cursor: pointer;">
                                <span class="fw-medium">{{ $project->name }}</span>
                            </td>
                            <td onclick="window.location='{{ route('projects.show', $project->id) }}'" style="cursor: pointer;">
                                @if($project->customer)
                                    <a href="{{ route('customers.show', $project->customer_id) }}" onclick="event.stopPropagation()">
                                        {{ $project->customer->name }}
                                    </a>
                                @endif
                            </td>
                            <td onclick="window.location='{{ route('projects.show', $project->id) }}'" style="cursor: pointer;">
                                @php
                                    $statusColors = ['planned' => 'secondary', 'active' => 'primary', 'on_hold' => 'warning', 'completed' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">
                                    {{ __($project->status) }}
                                </span>
                            </td>
                            <td onclick="window.location='{{ route('projects.show', $project->id) }}'" style="cursor: pointer;">{{ $project->due_date?->format('M d, Y') }}</td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('projects.edit', $project->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('projects.milestones', $project->id) }}" class="dropdown-item">
                                                <i class="bi bi-flag me-2"></i>{{ __('milestones') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('projects.destroy', $project->id) }}"
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
                    @if($projects->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            {{ __('showing') }} {{ $projects->count() }} {{ __('records') }}
        </div>
    </div>

    @include('shared.bulk-actions-script')
@endsection
