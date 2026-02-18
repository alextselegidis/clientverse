{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
--}}
@php use App\Enums\RoleEnum; @endphp
@extends('layouts.main-layout')
@section('pageTitle')
    {{ $user->name }}
@endsection
@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('users'), 'url' => session('users_list_url', route('setup.users'))],
        ['label' => $user->name]
    ]])
@endsection
@section('navActions')
    <a href="#" class="nav-link me-lg-3" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
    <form action="{{ route('setup.users.destroy', $user->id) }}"
          method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection
@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Edit Sidebar -->
        <div class="flex-shrink-0" style="min-width: 180px;">
            @include('shared.edit-sidebar', ['items' => [
                ['label' => __('details'), 'route' => 'setup.users.edit', 'params' => ['user' => $user->id], 'icon' => 'file-text']
            ]])
        </div>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('setup.users.update', ['user' => $user->id]) }}" method="POST" id="edit-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label text-dark small fw-medium">
                                        {{ __('name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control" required autofocus
                                           value="{{ old('name', $user?->name ?? null) }}">
                                    @error('name')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label text-dark small fw-medium">
                                        {{ __('email') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control" required
                                           value="{{ old('email', $user?->email ?? null) }}">
                                    @error('email')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label text-dark small fw-medium">
                                        {{ __('role') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="role" id="role" class="form-select" required>
                                        @foreach(RoleEnum::values() as $role)
                                            <option value="{{ $role }}" @if($user?->role === $role) selected @endif>
                                                {{ __($role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label text-dark small fw-medium">
                                        {{ __('password') }}
                                        <span class="text-muted small">({{ __('leave_blank_to_keep') }})</span>
                                    </label>
                                    <input type="password" id="password" name="password" class="form-control"
                                           value="{{ old('password') }}" autocomplete="new-password">
                                    @error('password')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password-confirmation" class="form-label text-dark small fw-medium">
                                        {{ __('password_repeat') }}
                                    </label>
                                    <input type="password" id="password-confirmation" name="password_confirmation" class="form-control"
                                           value="{{ old('password_confirmation') }}">
                                    @error('password_confirmation')
                                    <span class="form-text text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" id="is-active" name="is_active" class="form-check-input"
                                               value="1" {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}>
                                        <label for="is-active" class="form-check-label">
                                            {{ __('active') }}
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('inactive_users_cannot_login') }}</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Card Footer with Save Button -->
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @include('modals.create-modal', ['route' => route('setup.users.store')])
@endsection
