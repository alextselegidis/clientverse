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
    {{ __('pipeline') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('sales'), 'url' => route('sales')],
        ['label' => __('pipeline')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('sales') }}" class="nav-link me-lg-3">
        <i class="bi bi-list-ul me-2"></i>
        {{ __('list') }}
    </a>
    <a href="{{ route('sales.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    {{-- Each card carries a stage form, so moving works without JavaScript; dragging just submits it. --}}
    <div class="sales-board d-flex gap-3 pb-2">
        @foreach($columns as $stage => $column)
            <section class="sales-board-column flex-shrink-0 rounded-3 p-2" data-stage="{{ $stage }}"
                     aria-label="{{ $column['label'] }}">
                <h2 class="h6 d-flex justify-content-between px-1 pt-1 mb-2">
                    <span>{{ $column['label'] }}</span>
                    <span class="text-muted fw-normal">{{ $column['sales']->count() }}</span>
                </h2>
                @foreach($column['sales'] as $sale)
                    <article class="card border-0 shadow-sm mb-2" draggable="true" data-sale="{{ $sale->id }}">
                        <div class="card-body p-2">
                            <a href="{{ route('sales.show', $sale->id) }}" class="fw-medium d-block text-truncate">{{ $sale->name }}</a>
                            <div class="small text-muted text-truncate">{{ $sale->customer?->name }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
                                <span class="small">{{ $sale->value ? format_currency($sale->value, $sale->currency) : '' }}</span>
                                <form action="{{ route('sales.stage', $sale->id) }}" method="POST" data-no-unsaved-warning>
                                    @csrf
                                    @method('PATCH')
                                    <select name="stage" class="form-select form-select-sm" onchange="this.form.submit()"
                                            aria-label="{{ __('stage') }}">
                                        @foreach(\App\Models\Sale::stages() as $key => $label)
                                            <option value="{{ $key }}" @selected($key === $stage)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endforeach
    </div>
@endsection
