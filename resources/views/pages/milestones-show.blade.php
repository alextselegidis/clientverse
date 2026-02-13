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
    {{ $milestone->name }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects'), 'url' => route('projects')],
        ['label' => $project->name, 'url' => route('projects.show', $project->id)],
        ['label' => __('milestones'), 'url' => route('projects.milestones', $project->id)],
        ['label' => $milestone->name]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('projects.milestones.edit', [$project->id, $milestone->id]) }}" class="nav-link me-3">
        <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
    </a>
    <form action="{{ route('projects.milestones.destroy', [$project->id, $milestone->id]) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link text-danger">
            <i class="bi bi-trash me-2"></i>{{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1 {{ $milestone->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $milestone->name }}
                            </h4>
                            <a href="{{ route('projects.show', $project->id) }}" class="text-muted">
                                <i class="bi bi-folder me-1"></i>{{ $project->name }}
                            </a>
                        </div>
                        <div>
                            @if($milestone->is_completed)
                                <span class="badge bg-success fs-6">{{ __('completed') }}</span>
                            @else
                                <span class="badge bg-secondary fs-6">{{ __('pending') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <small class="text-muted d-block">{{ __('due_date') }}</small>
                            <span>{{ $milestone->due_date?->format('M d, Y') ?? '-' }}</span>
                        </div>
                    </div>

                    @if($milestone->description)
                        <div>
                            <h6 class="text-muted mb-2">{{ __('description') }}</h6>
                            <p class="mb-0">{!! nl2br(e($milestone->description)) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Details -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">{{ __('details') }}</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ __('created') }}</small>
                    <p class="mb-3">{{ $milestone->created_at->format('M d, Y H:i') }}</p>

                    <small class="text-muted">{{ __('updated') }}</small>
                    <p class="mb-0">{{ $milestone->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
