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
    {{ $milestone->exists ? __('edit_milestone') : __('create') . ' ' . __('milestone') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects'), 'url' => route('projects')],
        ['label' => $project->name, 'url' => route('projects.show', $project->id)],
        ['label' => __('milestones'), 'url' => route('projects.milestones', $project->id)],
        ['label' => $milestone->exists ? $milestone->name : __('create')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        @if($milestone->exists)
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'projects.milestones.edit', 'params' => ['project' => $project->id, 'milestone' => $milestone->id], 'icon' => 'file-text']
                ]])
            </div>
        @endif
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
            <form action="{{ $milestone->exists ? route('projects.milestones.update', [$project->id, $milestone->id]) : route('projects.milestones.store', $project->id) }}" method="POST">
                @csrf
                @if($milestone->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="name" class="form-label">
                            {{ __('name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="{{ old('name', $milestone->name) }}">
                        @error('name')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="due_date" class="form-label">{{ __('due_date') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control"
                               value="{{ old('due_date', $milestone->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $milestone->description) }}</textarea>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" id="is_completed" name="is_completed" class="form-check-input"
                           value="1" {{ old('is_completed', $milestone->is_completed) ? 'checked' : '' }}>
                    <label for="is_completed" class="form-check-label">{{ __('completed') }}</label>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('projects.milestones', $project->id) }}" class="btn btn-outline-secondary">
                        {{ __('cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
        </div>
    </div>
@endsection
