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
    {{ $project->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects'), 'url' => route('projects')],
        ['label' => $project->name]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('projects.create') }}" class="nav-link me-lg-3">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
    <a href="{{ route('projects.edit', $project->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
    </a>
    <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1">{{ $project->name }}</h4>
                            @if($project->customer)
                                <a href="{{ route('customers.show', $project->customer_id) }}" class="text-muted">
                                    <i class="bi bi-building me-1"></i>{{ $project->customer->name }}
                                </a>
                            @endif
                        </div>
                        <div>
                            @php
                                $statusColors = ['planned' => 'secondary', 'active' => 'primary', 'on_hold' => 'warning', 'completed' => 'success'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }} fs-6">
                                {{ __($project->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('visibility') }}</small>
                            <span>{{ __($project->visibility ?? 'internal') }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('start_date') }}</small>
                            <span>{{ $project->start_date?->format('M d, Y') ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('due_date') }}</small>
                            <span>{{ $project->due_date?->format('M d, Y') ?? '-' }}</span>
                        </div>
                    </div>

                    @if($project->description)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">{{ __('description') }}</h6>
                            <p class="mb-0">{!! nl2br(e($project->description)) !!}</p>
                        </div>
                    @endif

                    @if($project->notes)
                        <div>
                            <h6 class="text-muted mb-2">{{ __('notes') }}</h6>
                            <p class="mb-0">{!! nl2br(e($project->notes)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Milestones -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('milestones') }}</h5>
                    <a href="{{ route('projects.milestones.create', $project->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus"></i> {{ __('add') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($project->milestones->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($project->milestones as $milestone)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="{{ $milestone->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                            {{ $milestone->name }}
                                        </span>
                                        @if($milestone->due_date)
                                            <small class="text-muted ms-2">
                                                {{ __('due') }}: {{ $milestone->due_date->format('M d, Y') }}
                                            </small>
                                        @endif
                                    </div>
                                    <div>
                                        @if($milestone->is_completed)
                                            <span class="badge bg-success">{{ __('completed') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('pending') }}</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-flag display-6 d-block mb-2"></i>
                            {{ __('no_records_found') }}
                        </div>
                    @endif
                </div>
            </div>

            @include('shared.files-section', [
                'model' => $project,
                'uploadRoute' => route('projects.files.upload', $project->id),
                'downloadRoute' => 'projects.files.download',
                'deleteRoute' => 'projects.files.delete',
                'routeParams' => ['project' => $project->id],
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
                    <small class="text-muted">{{ __('created') }}</small>
                    <p class="mb-3">{{ $project->created_at->format('M d, Y H:i') }}</p>

                    <small class="text-muted">{{ __('updated') }}</small>
                    <p class="mb-0">{{ $project->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Related Contracts -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">{{ __('contracts') }}</h6>
                </div>
                <div class="card-body p-0">
                    @if($project->contracts->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($project->contracts as $contract)
                                <li class="list-group-item">
                                    <a href="{{ route('contracts.show', $contract->id) }}">
                                        {{ $contract->title }}
                                    </a>
                                    @php
                                        $contractStatusColors = ['draft' => 'secondary', 'active' => 'success', 'expired' => 'warning', 'cancelled' => 'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $contractStatusColors[$contract->status] ?? 'secondary' }} float-end">
                                        {{ __($contract->status) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-3">
                            {{ __('no_records_found') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
