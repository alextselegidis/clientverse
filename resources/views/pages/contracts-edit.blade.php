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
    {{ $contract->exists ? __('edit_contract') : __('create') . ' ' . __('contract') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('contracts'), 'url' => route('contracts')],
        ['label' => $contract->exists ? $contract->title : __('create')]
    ]])
@endsection

@section('navActions')
    @if($contract->exists)
        <a href="{{ route('contracts.create') }}" class="nav-link me-lg-3">
            <i class="bi bi-plus-square me-2"></i>
            {{ __('add') }}
        </a>
        <form action="{{ route('contracts.destroy', $contract->id) }}"
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
    @if($contract->exists)
        <div class="d-flex flex-column flex-lg-row gap-4">
            <!-- Edit Sidebar -->
            <div class="flex-shrink-0" style="min-width: 180px;">
                @include('shared.edit-sidebar', ['items' => [
                    ['label' => __('details'), 'route' => 'contracts.edit', 'params' => ['contract' => $contract->id], 'icon' => 'file-text']
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
            <form action="{{ $contract->exists ? route('contracts.update', $contract->id) : route('contracts.store') }}" method="POST" id="edit-form">
                @csrf
                @if($contract->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label">
                            {{ __('title') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="title" name="title" class="form-control" required autofocus
                               value="{{ old('title', $contract->title) }}">
                        @error('title')
                        <span class="form-text text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="type" class="form-label">{{ __('type') }}</label>
                        <select id="type" name="type" class="form-select">
                            @foreach(\App\Models\Contract::types() as $key => $label)
                                <option value="{{ $key }}" {{ old('type', $contract->type ?? 'fixed') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer_id" class="form-label">{{ __('customer') }}</label>
                        <select id="customer_id" name="customer_id" class="form-select">
                            <option value="">{{ __('select') }}...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->value }}" {{ old('customer_id', $contract->customer_id) == $customer->value ? 'selected' : '' }}>
                                    {{ $customer->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">{{ __('status') }}</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(\App\Models\Contract::statuses() as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $contract->status ?? 'draft') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">{{ __('project') }}</label>
                        <select id="project_id" name="project_id" class="form-select">
                            <option value="">{{ __('none') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->value }}" {{ old('project_id', $contract->project_id) == $project->value ? 'selected' : '' }}>
                                    {{ $project->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sale_id" class="form-label">{{ __('sale') }}</label>
                        <select id="sale_id" name="sale_id" class="form-select">
                            <option value="">{{ __('none') }}</option>
                            @foreach($sales as $sale)
                                <option value="{{ $sale->value }}" {{ old('sale_id', $contract->sale_id) == $sale->value ? 'selected' : '' }}>
                                    {{ $sale->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="value" class="form-label">{{ __('value') }}</label>
                        <input type="number" id="value" name="value" class="form-control" step="0.01"
                               value="{{ old('value', $contract->value) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">{{ __('start_date') }}</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                               value="{{ old('start_date', $contract->start_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">{{ __('end_date') }}</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                               value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $contract->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('notes') }}</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $contract->notes) }}</textarea>
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
