@extends('layouts.main-layout')

@section('pageTitle')
    {{$release->version}}
@endsection

@section('navTitle')
    {{__('view_release')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('add')}}
    </a>

    <a href="{{ route('releases.edit', ['release' => $release->id]) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{__('edit')}}
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
    <div class="d-flex flex-column-reverse flex-lg-row">
        <div class="flex-grow-0 sidebar-width">
            @include('shared.settings-sidebar')
        </div>

        <div class="flex-grow-1">
            @include('shared.show-title', ['title' => $release->version])

            <div class="d-lg-flex">
                <div class="w-100">

                    @include('shared.show-id', ['label' => __('id'), 'value' => $release->id])
                    @include('shared.show-text', ['label' => __('environment'), 'value' => $release->environment])
                    @include('shared.show-text', ['label' => __('project'), 'value' => $release->project?->name])
                    @include('shared.show-date-time', ['label' => __('release'), 'value' => $release->released_at])
                    @include('shared.show-textarea', ['label' => __('notes'), 'value' => $release->notes])
                </div>
                <div class="w-100">

                    @include('shared.show-date', ['label' => __('created'), 'value' => $release->created_at])
                    @include('shared.show-date', ['label' => __('updated'), 'value' => $release->updated_at])
                </div>
            </div>

        </div>
    </div>

    @include('modals.create-modal', ['route' => route('releases.store'), 'input_name' => 'version'])

@endsection
