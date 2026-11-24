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
    {{ __('tasks') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('tasks')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('tasks.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@php
    $sortUrl = fn ($column) => route('tasks', [
        'sort' => $column,
        'direction' => request('direction') === 'asc' ? 'desc' : 'asc',
        'q' => $q,
        'status' => $status,
        'mine' => $mine ?: null,
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
                    <form action="{{ route('tasks') }}" method="GET" class="row g-3 flex-grow-1">
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
                                @foreach(\App\Models\Task::statuses() as $key => $label)
                                    <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" id="mine" name="mine" value="1" class="form-check-input"
                                       onchange="this.form.submit()" @checked($mine)>
                                <label for="mine" class="form-check-label">{{ __('assigned_to_me') }}</label>
                            </div>
                        </div>
                    </form>
                    @include('shared.bulk-actions', ['tableId' => 'tasks', 'route' => route('tasks.bulk-destroy')])
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0" id="tasks-table">
                    <thead>
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input bulk-select-all" data-table="tasks">
                        </th>
                        <th class="w-100 w-md-auto">
                            <a href="{{ $sortUrl('title') }}" class="text-decoration-none">{{ __('title') }}{!! $sortIcon('title') !!}</a>
                        </th>
                        <th class="d-none d-lg-table-cell">{{ __('project') }}</th>
                        <th class="d-none d-lg-table-cell">{{ __('assigned_to') }}</th>
                        <th class="d-none d-md-table-cell text-nowrap">
                            <a href="{{ $sortUrl('due_date') }}" class="text-decoration-none">{{ __('due_date') }}{!! $sortIcon('due_date') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('status') }}" class="text-decoration-none">{{ __('status') }}{!! $sortIcon('status') !!}</a>
                        </th>
                        @include('shared.timestamp-headers')
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tasks as $task)
                        @php($showUrl = route('tasks.show', $task->id))
                        <tr>
                            <td class="ps-3" onclick="event.stopPropagation();">
                                <input type="checkbox" class="form-check-input bulk-select-item" data-table="tasks" value="{{ $task->id }}">
                            </td>
                            <td style="width: 32%;">
                                <a href="{{ $showUrl }}" class="fw-medium text-truncate d-block text-body text-decoration-none" style="width: 0; min-width: 100%;">
                                    @if($task->priority === 'high')
                                        <i class="bi bi-flag-fill text-danger me-1" title="{{ __('high') }}"></i>
                                    @endif
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td class="d-none d-lg-table-cell">
                                @if($task->project)
                                    <a href="{{ route('projects.show', $task->project_id) }}" onclick="event.stopPropagation()">{{ $task->project->name }}</a>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                                {{ $task->assignedUser?->name }}
                            </td>
                            <td class="d-none d-md-table-cell {{ $task->is_overdue ? 'text-danger' : '' }}">
                                {{ $task->due_date?->format('M d, Y') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ \App\Models\Task::statusColors()[$task->status] }}">{{ __($task->status) }}</span>
                            </td>
                            @include('shared.timestamp-cells', ['record' => $task])
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"
                                            aria-label="{{ __('actions') }}" title="{{ __('actions') }}">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('tasks.edit', $task->id) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('tasks.destroy', $task->id) }}"
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
                    @if($tasks->isEmpty())
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @include('shared.pagination', ['paginator' => $tasks])
        </div>
    </div>

    @include('shared.bulk-actions-script')
@endsection
