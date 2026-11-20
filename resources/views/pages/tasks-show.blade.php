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

        <div class="col-lg-4">
            @php($running = $task->timeEntries->first(fn ($entry) => !$entry->ended_at && $entry->user_id === auth()->id()))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('time_logged') }}</h5>
                    <span class="fw-medium">{{ format_minutes($task->minutes_logged) }}</span>
                </div>
                <div class="card-body border-bottom">
                    @if($running)
                        <form action="{{ route('tasks.timer.stop', $task->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-stop-circle me-2"></i>{{ __('stop_timer') }}
                                <span class="ms-1 opacity-75">({{ __('since') }} {{ $running->started_at->format('H:i') }})</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('tasks.timer.start', $task->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-light border w-100">
                                <i class="bi bi-play-circle me-2"></i>{{ __('start_timer') }}
                            </button>
                        </form>
                    @endif
                </div>
                <ul class="list-group list-group-flush">
                    @forelse($task->timeEntries as $entry)
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <div class="flex-grow-1">
                                <div class="fw-medium">
                                    {{ format_minutes($entry->minutes) }}
                                    @unless($entry->ended_at)
                                        <span class="badge bg-info ms-1">{{ __('running') }}</span>
                                    @endunless
                                </div>
                                <div class="small text-muted text-truncate">
                                    {{ $entry->started_at->format('M d, H:i') }} · {{ $entry->user?->name }}
                                    @if($entry->note) · {{ $entry->note }} @endif
                                </div>
                            </div>
                            <form action="{{ route('tasks.time-entries.destroy', [$task->id, $entry->id]) }}" method="POST"
                                  onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon" aria-label="{{ __('delete') }}" title="{{ __('delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ __('no_records_found') }}</li>
                    @endforelse
                </ul>
                <div class="card-body border-top">
                    <form action="{{ route('tasks.time-entries.store', $task->id) }}" method="POST" id="time-entry-form">
                        @csrf
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="started_at" class="form-label">{{ __('start') }}</label>
                                <input type="datetime-local" id="started_at" name="started_at" class="form-control" required
                                       value="{{ old('started_at') }}">
                            </div>
                            <div class="col-6">
                                <label for="ended_at" class="form-label">{{ __('end') }}</label>
                                <input type="datetime-local" id="ended_at" name="ended_at" class="form-control" required
                                       value="{{ old('ended_at') }}">
                            </div>
                            <div class="col-12">
                                <label for="note" class="form-label">{{ __('note') }}</label>
                                <input type="text" id="note" name="note" class="form-control" maxlength="255" value="{{ old('note') }}">
                            </div>
                        </div>
                        @include('shared.errors', ['class' => 'mt-3 mb-0'])
                        <button type="submit" class="btn btn-light border w-100 mt-3">{{ __('log_time') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
