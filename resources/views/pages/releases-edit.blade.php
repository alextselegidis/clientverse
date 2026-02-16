@extends('layouts.main-layout')

@section('pageTitle')
    {{$release->version}}
@endsection

@section('navTitle')
    {{__('edit_release')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('create')}}
    </a>

    <form action="{{route('releases.destroy', $release->id)}}"
          method="POST"
          onsubmit="return confirm('{{__('delete_record_prompt')}}')">
        @csrf
        @method('DELETE')

        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{__('delete')}}
        </button>
    </form>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Edit Sidebar -->
        <div class="flex-shrink-0" style="min-width: 180px;">
            @include('shared.edit-sidebar', ['items' => [
                ['label' => __('details'), 'route' => 'releases.edit', 'params' => ['release' => $release->id], 'icon' => 'file-text']
            ]])
        </div>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
            <form action="{{route('releases.update', ['release' => $release->id])}}" method="POST" id="edit-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="version" class="form-label">
                        {{ __('version') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="version" name="version" class="form-control" required
                           value="{{ old('version', $release?->version ?? null) }}">
                    @error('version')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="environment" class="form-label">
                        {{ __('environment') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="environment" name="environment" class="form-control" required
                           value="{{ old('environment', $release?->environment ?? null) }}">
                    @error('environment')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="project-id" class="form-label">
                        {{ __('project') }}
                    </label>
                    <select id="project-id" name="project_id" class="form-select">
                        <option value="" @if(empty(old('project-id', $release?->project_id ?? null))) selected @endif></option>
                        @foreach($projectOptions as $projectOption)
                            <option value="{{$projectOption->value}}" @if($release?->project_id === $projectOption->value) selected @endif>{{$projectOption->label}}</option>
                        @endforeach
                    </select>
                    @error('project_id')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="released-at" class="form-label">
                        {{ __('release') }}
                    </label>
                    <input type="datetime-local" id="released-at" name="released_at" class="form-control"
                           value="{{ old('released_at', $project?->released_at ?? null) }}">
                    @error('released_at')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">
                        {{ __('notes') }}
                    </label>
                    <textarea id="notes" name="notes" class="form-control" rows="7">{{ old('notes', $release?->notes ?? null) }}</textarea>
                    @error('notes')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </form>
        </div>
        <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
            <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">
                {{__('cancel')}}
            </button>
            <button type="submit" form="edit-form" class="btn btn-dark">
                {{__('save')}}
            </button>
        </div>
    </div>
        </div>
    </div>

    @include('modals.create-modal', ['route' => route('releases.store'), 'input_name' => 'version'])

@endsection
