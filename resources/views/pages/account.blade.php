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

@extends('layouts.main-layout')

@section('pageTitle')
    {{__('account')}}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('account')]
    ]])
@endsection

@section('content')
    <div>
        <div style="max-width: 600px" class="mx-auto my-4">

            <!-- Account Details Card -->
            <h5 class="text-dark fw-bold mb-3">{{ __('profile') }}</h5>

            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('account.update') }}" method="POST" id="account-form">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label text-dark fw-medium">
                                <span class="text-danger">*</span> {{ __('name') }}
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', auth()->user()->name) }}"
                                required
                            >
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label text-dark fw-medium">
                                <span class="text-danger">*</span> {{ __('email') }}
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', auth()->user()->email) }}"
                                required
                            >
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>

                <!-- Card Footer with Save Button -->
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="account-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>
            <!-- Change Password Section -->
            <h5 class="text-dark fw-bold mb-3">{{ __('password') }}</h5>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('account.update') }}" method="POST" id="password-form">
                        @csrf
                        @method('PUT')

                        <!-- Hidden fields to preserve account data -->
                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">

                        <!-- Current Password -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label text-dark fw-medium">
                                {{ __('current_password') }}
                            </label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                autocomplete="current-password"
                            >
                            @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label text-dark fw-medium">
                                {{ __('new_password') }}
                            </label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="new-password"
                            >
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label text-dark fw-medium">
                                {{ __('password_repeat') }}
                            </label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                autocomplete="new-password"
                            >
                        </div>
                    </form>
                </div>

                <!-- Card Footer with Save Button -->
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="password-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>

        </div>

    </div>
@endsection
