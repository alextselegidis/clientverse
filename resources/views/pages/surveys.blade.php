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
    {{ __('surveys') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('surveys')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('surveys.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="card card-table border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-light-header align-middle mb-0">
                    <thead>
                    <tr>
                        <th class="ps-3 w-100 w-md-auto">{{ __('title') }}</th>
                        <th class="d-none d-md-table-cell text-nowrap">{{ __('responses') }}</th>
                        <th class="pe-3">{{ __('status') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($surveys as $survey)
                        <tr onclick="window.location='{{ route('surveys.show', $survey->id) }}'" style="cursor: pointer;">
                            <td class="ps-3" style="width: 32%;">
                                <div class="fw-medium text-truncate" style="width: 0; min-width: 100%;">{{ $survey->title }}</div>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $survey->responses_count }}</td>
                            <td class="pe-3">
                                <span class="badge bg-{{ $survey->is_open ? 'success' : 'secondary' }}">{{ $survey->is_open ? __('open') : __('closed') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                {{ __('no_records_found') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @include('shared.pagination', ['paginator' => $surveys])
        </div>
    </div>
@endsection
