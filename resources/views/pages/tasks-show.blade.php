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
    {{ $task->title }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('tasks'), 'url' => route('tasks')],
        ['label' => $task->title]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('tasks.edit', $task->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex align-items-center gap-2">
                    <h5 class="mb-0 text-truncate">{{ $task->title }}</h5>
                    <span class="badge bg-{{ \App\Models\Task::statusColors()[$task->status] }}">{{ __($task->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('priority') }}:</strong> {{ __($task->priority) }}</p>
                            <p class="mb-2 {{ $task->is_overdue ? 'text-danger' : '' }}">
                                <strong>{{ __('due_date') }}:</strong> {{ $task->due_date?->format('M d, Y') ?? '-' }}
                            </p>
                            <p class="mb-2"><strong>{{ __('assigned_to') }}:</strong> {{ $task->assignedUser?->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('project') }}:</strong>
                                @if($task->project)
                                    <a href="{{ route('projects.show', $task->project_id) }}">{{ $task->project->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('customer') }}:</strong>
                                @if($task->customer)
                                    <a href="{{ route('customers.show', $task->customer_id) }}">{{ $task->customer->name }}</a>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($task->description)
                        <hr>
                        <p class="text-muted mb-0">{!! nl2br(e($task->description)) !!}</p>
                    @endif
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    {{ __('created') }}: {{ $task->created_at->format('M d, Y') }} |
                    {{ __('updated') }}: {{ $task->updated_at->format('M d, Y') }}
                </div>
            </div>
        </div>
    </div>
@endsection
