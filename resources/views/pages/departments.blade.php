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
    {{ __('departments') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('departments')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('setup.departments.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <div class="flex-grow-1">
            <div class="card card-table border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-light-header align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="ps-3 w-100 w-md-auto">{{ __('name') }}</th>
                                <th class="text-nowrap">{{ __('tickets') }}</th>
                                <th class="pe-3 text-end" style="width: 100px;"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($departments as $department)
                                @php($editUrl = route('setup.departments.edit', $department->id))
                                <tr>
                                    <td class="ps-3" style="width: 32%;">
                                        <a href="{{ $editUrl }}" class="fw-medium text-truncate d-block text-body text-decoration-none" style="width: 0; min-width: 100%;">{{ $department->name }}</a>
                                    </td>
                                    <td>
                                        <a href="{{ route('tickets', ['department_id' => $department->id]) }}" onclick="event.stopPropagation()">{{ $department->tickets_count }}</a>
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
                                                    <form action="{{ route('setup.departments.destroy', $department->id) }}" method="POST"
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
                            @if($departments->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        {{ __('no_records_found') }}
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    @include('shared.pagination', ['paginator' => $departments])
                </div>
            </div>
        </div>
    </div>
@endsection
