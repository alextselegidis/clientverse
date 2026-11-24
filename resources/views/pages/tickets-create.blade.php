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
    {{ __('create') }} {{ __('ticket') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('tickets'), 'url' => route('tickets')],
        ['label' => __('create')]
    ]])
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('tickets.store') }}" method="POST" id="edit-form">
                        @csrf

                        <div class="mb-3">
                            <label for="subject" class="form-label">{{ __('subject') }} <span class="text-danger">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('subject') }}">
                            @error('subject')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">{{ __('customer') }} <span class="text-danger">*</span></label>
                                <select id="customer_id" name="customer_id" class="form-select" required>
                                    <option value="">{{ __('select') }}...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->value }}" @selected(old('customer_id', $ticket->customer_id) == $customer->value)>{{ $customer->label }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">{{ __('priority') }}</label>
                                <select id="priority" name="priority" class="form-select">
                                    @foreach(\App\Models\Task::priorities() as $key => $label)
                                        <option value="{{ $key }}" @selected(old('priority', $ticket->priority) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @include('shared.department-select', ['class' => 'col-md-6 mb-3'])
                            <div class="col-md-6 mb-3">
                                <label for="assigned_to" class="form-label">{{ __('assigned_to') }}</label>
                                <select id="assigned_to" name="assigned_to" class="form-select">
                                    <option value="">-</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">{{ __('description') }} <span class="text-danger">*</span></label>
                            <textarea id="body" name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea>
                            @error('body')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
