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
    {{ __('milestones') }} - {{ $project->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects'), 'url' => route('projects')],
        ['label' => $project->name, 'url' => route('projects.show', $project->id)],
        ['label' => __('milestones')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('projects.milestones.create', $project->id) }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card card-table border-0 shadow-sm">
        <div class="card-body">
            <!-- Search -->
            <div class="table-filters">
                <form action="{{ route('projects.milestones', $project->id) }}" method="GET" class="row g-3">
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
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-light-header align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-3">
                            <a href="{{ route('projects.milestones', [$project->id, 'sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                {{ __('name') }}
                                @if(request('sort') === 'name')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('projects.milestones', [$project->id, 'sort' => 'due_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                {{ __('due_date') }}
                                @if(request('sort') === 'due_date')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route('projects.milestones', [$project->id, 'sort' => 'is_completed', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'q' => $q]) }}" class="text-decoration-none">
                                {{ __('status') }}
                                @if(request('sort') === 'is_completed')
                                    <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </a>
                        </th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($milestones as $milestone)
                        <tr onclick="window.location='{{ route('projects.milestones.show', [$project->id, $milestone->id]) }}'" style="cursor: pointer;">
                            <td class="ps-3">
                                <span class="fw-medium {{ $milestone->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $milestone->name }}
                                </span>
                            </td>
                            <td>{{ $milestone->due_date?->format('M d, Y') ?? '-' }}</td>
                            <td>
                                @if($milestone->is_completed)
                                    <span class="badge bg-success">{{ __('completed') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('pending') }}</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        {{ __('actions') }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ route('projects.milestones.edit', [$project->id, $milestone->id]) }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('projects.milestones.destroy', [$project->id, $milestone->id]) }}"
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
                    @if($milestones->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
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
            {{ __('showing') }} {{ $milestones->firstItem() ?? 0 }} - {{ $milestones->lastItem() ?? 0 }} {{ __('of') }} {{ $milestones->total() }} {{ __('records') }}
        </div>
    </div>
@endsection
