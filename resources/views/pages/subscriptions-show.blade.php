@extends('layouts.main-layout')

@section('pageTitle')
    {{$subscription->plan}}
@endsection

@section('navTitle')
    {{__('edit_subscription')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('add')}}
    </a>

    <a href="{{ route('subscriptions.edit', ['subscription' => $subscription->id]) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{__('edit')}}
    </a>

    <form action="{{route('subscriptions.destroy', $subscription->id)}}"
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
            @include('shared.show-title', ['title' => $subscription->plan])

            <div class="d-lg-flex">
                <div class="w-100">
                    @include('shared.show-id', ['label' => __('id'), 'value' => $subscription->id])
                    @include('shared.show-text', ['label' => __('customer'), 'value' => $subscription->customer?->display_name])
                    @include('shared.show-date', ['label' => __('start_date'), 'value' => $subscription->start_date])
                    @include('shared.show-date', ['label' => __('end_date'), 'value' => $subscription->end_date])
                    @include('shared.show-bool', ['label' => __('active'), 'value' => $subscription->is_active])
                    @include('shared.show-textarea', ['label' => __('notes'), 'value' => $subscription->notes])
                </div>
                <div class="w-100">
                    @include('shared.show-date', ['label' => __('created'), 'value' => $subscription->created_at])
                    @include('shared.show-date', ['label' => __('updated'), 'value' => $subscription->updated_at])
                </div>
            </div>

        </div>
    </div>

    @include('modals.create-modal', ['route' => route('subscriptions.store'), 'input_name' => 'plan'])

@endsection
