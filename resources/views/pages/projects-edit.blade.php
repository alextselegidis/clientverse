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

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ $project->exists ? route('projects.update', $project->id) : route('projects.store') }}" method="POST">
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
                                <input type="text" id="name" name="name" class="form-control" required
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

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ $project->exists ? route('projects.show', $project->id) : route('projects') }}" class="btn btn-outline-secondary">
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
