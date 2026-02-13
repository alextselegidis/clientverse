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
        ['label' => $contact->exists ? $contact->name : __('create')]
    ]])
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ $contact->exists ? route('customers.contacts.update', [$customer->id, $contact->id]) : route('customers.contacts.store', $customer->id) }}" method="POST">
                        @csrf
                        @if($contact->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    {{ __('name') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" name="name" class="form-control" required
                                       value="{{ old('name', $contact->name) }}">
                                @error('name')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">{{ __('position') }}</label>
                                <input type="text" id="position" name="position" class="form-control"
                                       value="{{ old('position', $contact->position) }}">
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
                                <label for="role" class="form-label">{{ __('role') }}</label>
                                <select id="role" name="role" class="form-select">
                                    @foreach(\App\Models\Contact::roles() as $key => $label)
                                        <option value="{{ $key }}" {{ old('role', $contact->role ?? 'other') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">&nbsp;</label>
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

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ $contact->exists ? route('customers.contacts.show', [$customer->id, $contact->id]) : route('customers.contacts', $customer->id) }}" class="btn btn-outline-secondary">
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
