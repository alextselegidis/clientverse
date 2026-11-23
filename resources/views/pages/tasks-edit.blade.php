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
    {{ $task->exists ? $task->title : __('create') . ' ' . __('task') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('tasks'), 'url' => route('tasks')],
        // An existing record links back to its page, so the trail leads out of the form.
        ...($task->exists
            ? [['label' => $task->title, 'url' => route('tasks.show', $task->id)], ['label' => __('edit')]]
            : [['label' => __('create')]]),
    ]])
@endsection

@section('navActions')
    @if($task->exists)
        <a href="{{ route('tasks.show', $task->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-eye me-2"></i>
            {{ __('view') }}
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
    @endif
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $task->exists ? route('tasks.update', $task->id) : route('tasks.store') }}" method="POST" id="edit-form">
                        @csrf
                        @if($task->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('title') }} <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('title', $task->title) }}">
                            @error('title')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">{{ __('status') }}</label>
                                <select id="status" name="status" class="form-select">
                                    @foreach(\App\Models\Task::statuses() as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $task->status ?? 'todo') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">{{ __('priority') }}</label>
                                <select id="priority" name="priority" class="form-select">
                                    @foreach(\App\Models\Task::priorities() as $key => $label)
                                        <option value="{{ $key }}" @selected(old('priority', $task->priority ?? 'normal') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="due_date" class="form-label">{{ __('due_date') }}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control"
                                       value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="assigned_to" class="form-label">{{ __('assigned_to') }}</label>
                                <select id="assigned_to" name="assigned_to" class="form-select">
                                    <option value="">-</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(old('assigned_to', $task->assigned_to) == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="project_id" class="form-label">{{ __('project') }}</label>
                                <select id="project_id" name="project_id" class="form-select">
                                    <option value="">-</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->value }}" @selected(old('project_id', $task->project_id) == $project->value)>{{ $project->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="customer_id" class="form-label">{{ __('customer') }}</label>
                                <select id="customer_id" name="customer_id" class="form-select">
                                    <option value="">-</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->value }}" @selected(old('customer_id', $task->customer_id) == $customer->value)>{{ $customer->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('description') }}</label>
                            <textarea id="description" name="description" class="form-control" rows="5">{{ old('description', $task->description) }}</textarea>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
