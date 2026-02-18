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
    {{ $contact->exists ? __('edit_contact') : __('create') . ' ' . __('contact') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('customers'), 'url' => route('customers')],
        ['label' => $customer->name, 'url' => route('customers.show', $customer->id)],
        ['label' => __('contacts'), 'url' => route('customers.contacts', $customer->id)],
        ['label' => $contact->exists ? $contact->full_name : __('create')]
    ]])
@endsection

@section('navActions')
    @if($contact->exists)
        <a href="{{ route('customers.contacts.create', $customer->id) }}" class="nav-link me-lg-3">
            <i class="bi bi-plus-square me-2"></i>
            {{ __('add') }}
        </a>
        <form action="{{ route('customers.contacts.destroy', [$customer->id, $contact->id]) }}"
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
    @if($contact->exists)
        <div class="d-flex flex-column flex-lg-row gap-4">
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'customers.contacts.edit', 'params' => ['customer' => $customer->id, 'contact' => $contact->id], 'icon' => 'file-text']
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
            <form action="{{ $contact->exists ? route('customers.contacts.update', [$customer->id, $contact->id]) : route('customers.contacts.store', $customer->id) }}" method="POST" id="edit-form">
                @csrf
                @if($contact->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">
                            {{ __('first_name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name" class="form-control" required autofocus
                               value="{{ old('first_name', $contact->first_name) }}">
                        @error('first_name')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">{{ __('last_name') }}</label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                               value="{{ old('last_name', $contact->last_name) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">{{ __('email') }}</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email', $contact->email) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">{{ __('phone') }}</label>
                        <input type="text" id="phone" name="phone" class="form-control"
                               value="{{ old('phone', $contact->phone) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label">{{ __('position') }}</label>
                        <input type="text" id="position" name="position" class="form-control"
                               value="{{ old('position', $contact->position) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">{{ __('role') }}</label>
                        <select id="role" name="role" class="form-select">
                            @foreach(\App\Models\Contact::roles() as $key => $label)
                                <option value="{{ $key }}" {{ old('role', $contact->role ?? 'other') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" value="1"
                                   {{ old('is_primary', $contact->is_primary) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_primary">{{ __('is_primary') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="has_portal_access" name="has_portal_access" value="1"
                                   {{ old('has_portal_access', $contact->has_portal_access) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_portal_access">{{ __('portal_access') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $contact->notes) }}</textarea>
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
