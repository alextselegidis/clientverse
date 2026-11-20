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
    {{ __('items') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('items')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('items.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@php
    $sortUrl = fn ($column) => route('items', [
        'sort' => $column,
        'direction' => request('direction') === 'asc' ? 'desc' : 'asc',
        'q' => $q,
    ]);
    $sortIcon = fn ($column) => request('sort') === $column
        ? '<i class="bi bi-chevron-' . (request('direction') === 'asc' ? 'up' : 'down') . ' ms-1"></i>'
        : '';
@endphp

@section('content')
    <div class="card card-table border-0 shadow-sm">
        <div class="card-body">
            <div class="table-filters">
                <form action="{{ route('items') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="q" name="q" class="form-control border-start-0"
                                   value="{{ $q }}" placeholder="{{ __('search') }}...">
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-3 w-100 w-md-auto">
                            <a href="{{ $sortUrl('description') }}" class="text-decoration-none">{{ __('description') }}{!! $sortIcon('description') !!}</a>
                        </th>
                        <th class="text-nowrap">
                            <a href="{{ $sortUrl('unit_price') }}" class="text-decoration-none">{{ __('unit_price') }}{!! $sortIcon('unit_price') !!}</a>
                        </th>
                        <th class="d-none d-md-table-cell text-nowrap">{{ __('tax') }}</th>
                        <th class="pe-3 text-end" style="width: 100px;"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $item)
                        @php($editUrl = route('items.edit', $item->id))
                        <tr>
                            <td class="ps-3" onclick="window.location='{{ $editUrl }}'" style="cursor: pointer; width: 32%;">
                                <div class="fw-medium text-truncate" style="width: 0; min-width: 100%;">{{ $item->description }}</div>
                            </td>
                            <td class="text-nowrap" onclick="window.location='{{ $editUrl }}'" style="cursor: pointer;">
                                {{ format_currency($item->unit_price) }}
                            </td>
                            <td class="d-none d-md-table-cell" onclick="window.location='{{ $editUrl }}'" style="cursor: pointer;">
                                {{ rtrim(rtrim($item->tax_rate, '0'), '.') }}%
                            </td>
                            <td class="pe-3 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown"
                                            aria-label="{{ __('actions') }}" title="{{ __('actions') }}">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a href="{{ $editUrl }}" class="dropdown-item">
                                                <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('items.destroy', $item->id) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>{{ __('delete') }}
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($items->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>

            @include('shared.pagination', ['paginator' => $items])
        </div>
    </div>
@endsection
