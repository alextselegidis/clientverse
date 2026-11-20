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
    {{ $item->exists ? $item->description : __('create') . ' ' . __('item') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('items'), 'url' => route('items')],
        ['label' => $item->exists ? $item->description : __('create')]
    ]])
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ $item->exists ? route('items.update', $item->id) : route('items.store') }}" method="POST" id="edit-form">
                        @csrf
                        @if($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                {{ __('description') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="description" name="description" class="form-control" required autofocus maxlength="255"
                                   value="{{ old('description', $item->description) }}">
                            @error('description')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="unit_price" class="form-label">{{ __('unit_price') }} <span class="text-danger">*</span></label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" step="0.01" required
                                       value="{{ old('unit_price', $item->unit_price) }}">
                                @error('unit_price')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">{{ __('tax') }} %</label>
                                <input type="number" id="tax_rate" name="tax_rate" class="form-control" step="0.01" min="0" max="100" required
                                       value="{{ old('tax_rate', $item->tax_rate) }}">
                                @error('tax_rate')
                                <span class="form-text text-danger">{{ $message }}</span>
                                @enderror
                            </div>
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
