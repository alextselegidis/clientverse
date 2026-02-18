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
    {{ $project->exists ? __('edit_project') : __('create') . ' ' . __('project') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('projects'), 'url' => route('projects')],
        ['label' => $project->exists ? $project->name : __('create')]
    ]])
@endsection

@section('navActions')
    @if($project->exists)
        <a href="{{ route('projects.create') }}" class="nav-link me-lg-3">
            <i class="bi bi-plus-square me-2"></i>
            {{ __('add') }}
        </a>
        <form action="{{ route('projects.destroy', $project->id) }}"
              method="POST"
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
    @if($project->exists)
        <div class="d-flex flex-column flex-lg-row gap-4">
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'projects.edit', 'params' => ['project' => $project->id], 'icon' => 'file-text']
                ]])
            </div>
            <!-- Main Content -->
            <div class="flex-grow-1">
    @else
        <div class="row justify-content-center">
            <div class="col-lg-8">
    @endif
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
            <form action="{{ $project->exists ? route('projects.update', $project->id) : route('projects.store') }}" method="POST" id="edit-form">
                @csrf
                @if($project->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            {{ __('name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="form-control" required autofocus
                               value="{{ old('name', $project->name) }}">
                        @error('name')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="customer_id" class="form-label">{{ __('customer') }}</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">{{ __('select') }}...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->value }}" {{ old('customer_id', $project->customer_id) == $customer->value ? 'selected' : '' }}>
                                    {{ $customer->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(\App\Models\Project::statuses() as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $project->status ?? 'planned') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="visibility" class="form-label">{{ __('visibility') }}</label>
                        <select id="visibility" name="visibility" class="form-select">
                            @foreach(\App\Models\Project::visibilities() as $key => $label)
                                <option value="{{ $key }}" {{ old('visibility', $project->visibility ?? 'internal') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">{{ __('start_date') }}</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                               value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">{{ __('due_date') }}</label>
                        <input type="date" id="due_date" name="due_date" class="form-control"
                               value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $project->notes) }}</textarea>
                </div>
            </form>
        </div>
        <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
            <button type="submit" form="edit-form" class="btn btn-dark">
                {{ __('save') }}
            </button>
        </div>
    </div>
        </div>
    </div>
@endsection
