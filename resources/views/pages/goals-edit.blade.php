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
    {{ $goal->exists ? $goal->name : __('create') . ' ' . __('goal') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('goals'), 'url' => route('goals')],
        ['label' => $goal->exists ? $goal->name : __('create')]
    ]])
@endsection

@section('navActions')
    @if($goal->exists)
        <form action="{{ route('goals.destroy', $goal->id) }}" method="POST"
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $goal->exists ? route('goals.update', $goal->id) : route('goals.store') }}" method="POST" id="edit-form">
                        @csrf
                        @if($goal->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('name') }} <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('name', $goal->name) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="metric" class="form-label">{{ __('metric') }}</label>
                                <select id="metric" name="metric" class="form-select">
                                    @foreach(\App\Models\Goal::metrics() as $key => $label)
                                        <option value="{{ $key }}" @selected(old('metric', $goal->metric) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="target" class="form-label">{{ __('target') }} <span class="text-danger">*</span></label>
                                <input type="number" id="target" name="target" class="form-control" step="0.01" min="0.01" required
                                       value="{{ old('target', $goal->target) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">{{ __('currency') }}</label>
                                <input type="text" id="currency" name="currency" class="form-control" maxlength="3"
                                       value="{{ old('currency', $goal->currency) }}">
                                <div class="form-text">{{ __('goal_currency_hint') }}</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">{{ __('start_date') }}</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" required
                                       value="{{ old('start_date', $goal->start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">{{ __('end_date') }}</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" required
                                       value="{{ old('end_date', $goal->end_date?->format('Y-m-d')) }}">
                            </div>
                        </div>

                        @include('shared.errors', ['class' => 'mb-0'])
                    </form>
                </div>
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
