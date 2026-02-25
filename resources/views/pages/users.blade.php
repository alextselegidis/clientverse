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
    {{ __('users') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('users')]
    ]])
@endsection

@section('navActions')
    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#create-modal">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('add') }}
    </a>
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Sidebar -->
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>
        <!-- Main Content -->
        <div class="flex-grow-1">
            <div class="card card-table border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <!-- Search -->
                    <div class="table-filters">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <form action="{{ route('setup.users') }}" method="GET">
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" id="q" name="q" class="form-control border-start-0"
                                           value="{{ $q }}"
                                           placeholder="{{ __('search') }}..." style="max-width: 300px;">
                                </div>
                            </form>
                            @include('shared.bulk-actions', ['tableId' => 'users', 'route' => route('setup.users.bulk-destroy')])
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-striped table-hover table-light-header align-middle mb-0" id="users-table">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input bulk-select-all" data-table="users">
                                    </th>
                                    <th>
                                        <a href="{{ route('setup.users', ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">
                                            {{ __('name') }}
                                            @if(request('sort') === 'name')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('setup.users', ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">
                                            {{ __('email') }}
                                            @if(request('sort') === 'email')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('setup.users', ['sort' => 'role', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">
                                            {{ __('role') }}
                                            @if(request('sort') === 'role')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ route('setup.users', ['sort' => 'is_active', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none">
                                            {{ __('active') }}
                                            @if(request('sort') === 'is_active')
                                                <i class="bi bi-chevron-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="pe-4 text-end" style="width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td class="ps-4" onclick="event.stopPropagation();">
                                            <input type="checkbox" class="form-check-input bulk-select-item" data-table="users" value="{{ $user->id }}">
                                        </td>
                                        <td onclick="window.location='{{ route('setup.users.edit', $user->id) }}'" style="cursor: pointer;">
                                            <span class="fw-medium">{{ $user->name }}</span>
                                        </td>
                                        <td onclick="window.location='{{ route('setup.users.edit', $user->id) }}'" style="cursor: pointer;">
                                            <a href="mailto:{{ $user->email }}" class="text-decoration-none" onclick="event.stopPropagation();">
                                                {{ $user->email }}
                                            </a>
                                        </td>
                                        <td onclick="window.location='{{ route('setup.users.edit', $user->id) }}'" style="cursor: pointer;">
                                            <span class="badge bg-light text-dark">{{ __($user->role) }}</span>
                                        </td>
                                        <td onclick="window.location='{{ route('setup.users.edit', $user->id) }}'" style="cursor: pointer;">
                                            @if($user->is_active)
                                                <span class="badge bg-success-subtle text-success">{{ __('yes') }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ __('no') }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="dropdown" onclick="event.stopPropagation();">
                                                <button class="btn btn-sm btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    {{ __('actions') }}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a href="{{ route('setup.users.edit', ['user' => $user->id]) }}" class="dropdown-item">
                                                            <i class="bi bi-pencil me-2"></i>{{ __('edit') }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('setup.users.destroy', $user->id) }}"
                                                              method="POST"
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
                                @if($users->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                            {{ __('no_records_found') }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @include('shared.pagination', ['paginator' => $users])
                </div>
            </div>
        </div>
    </div>
    @include('modals.create-modal', ['route' => route('setup.users.store')])
    @include('shared.bulk-actions-script')
@endsection
