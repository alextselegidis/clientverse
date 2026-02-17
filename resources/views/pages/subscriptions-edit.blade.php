@extends('layouts.main-layout')

@section('pageTitle')
    {{$subscription->plan}}
@endsection

@section('navTitle')
    {{__('edit_subscriptions')}}
@endsection

@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{__('add')}}
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
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Edit Sidebar -->
        <div class="flex-shrink-0" style="min-width: 180px;">
            @include('shared.edit-sidebar', ['items' => [
                ['label' => __('details'), 'route' => 'subscriptions.edit', 'params' => ['subscription' => $subscription->id], 'icon' => 'file-text']
            ]])
        </div>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
            <form action="{{route('subscriptions.update', ['subscription' => $subscription->id])}}" method="POST" id="edit-form">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="plan" class="form-label">
                        {{ __('plan') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="plan" name="plan" class="form-control" required
                           value="{{ old('plan', $subscription?->plan ?? null) }}">
                    @error('plan')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="customer-id" class="form-label">
                        {{ __('customer') }}
                    </label>
                    <select id="customer-id" name="customer_id" class="form-select">
                        <option value="" @if(empty(old('customer-id', $subscription?->customer_id ?? null))) selected @endif></option>
                        @foreach($customerOptions as $customerOption)
                            <option value="{{$customerOption->value}}" @if($subscription?->customer_id === $customerOption->value) selected @endif>{{$customerOption->label}}</option>
                        @endforeach
                    </select>
                    @error('customer_id')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="start-date" class="form-label">
                        {{ __('start_date') }}
                    </label>
                    <input type="date" id="start-date" name="start_date" class="form-control"
                           value="{{ old('start_date', $subscription?->start_date ?? null) }}">
                    @error('start_date')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="end-date" class="form-label">
                        {{ __('end_date') }}
                    </label>
                    <input type="date" id="end-date" name="end_date" class="form-control"
                           value="{{ old('end_date', $subscription?->end_date ?? null) }}">
                    @error('end_date')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" id="is-active" name="is_active" class="form-check-input"
                           value="1" {{ old('is_active', $subscription?->is_active) ? 'checked' : '' }}>
                    <label for="is-active" class="form-check-label">
                        {{ __('active') }}
                    </label>
                    @error('is_active')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">
                        {{ __('notes') }}
                    </label>
                    <textarea id="notes" name="notes" class="form-control" rows="7">{{ old('notes', $subscription?->notes ?? null) }}</textarea>
                    @error('notes')
                    <span class="form-text text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </form>
        </div>
        <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
            <button type="submit" form="edit-form" class="btn btn-dark">
                {{__('save')}}
            </button>
        </div>
    </div>
        </div>
    </div>

    @include('modals.create-modal', ['route' => route('subscriptions.store'), 'input_name' => 'plan'])

@endsection
