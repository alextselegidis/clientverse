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
    {{ __('announcements') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('announcements')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('setup.announcements.create') }}" class="nav-link">
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
                                <th class="ps-3 w-100 w-md-auto">{{ __('title') }}</th>
                                <th class="text-nowrap">{{ __('audience') }}</th>
                                <th class="d-none d-md-table-cell text-nowrap">{{ __('visible_until') }}</th>
                                <th class="pe-3 text-end" style="width: 100px;"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($announcements as $announcement)
                                @php($editUrl = route('setup.announcements.edit', $announcement->id))
                                <tr>
                                    <td class="ps-3" onclick="window.location='{{ $editUrl }}'" style="cursor: pointer; width: 32%;">
                                        <div class="fw-medium text-truncate {{ $announcement->isExpired() ? 'text-muted' : '' }}"
                                             style="width: 0; min-width: 100%;">{{ $announcement->title }}</div>
                                    </td>
                                    <td onclick="window.location='{{ $editUrl }}'" style="cursor: pointer;">
                                        <span class="badge bg-{{ $announcement->audience === 'staff' ? 'secondary' : 'info' }}">
                                            {{ \App\Models\Announcement::audiences()[$announcement->audience] }}
                                        </span>
                                    </td>
                                    <td class="d-none d-md-table-cell" onclick="window.location='{{ $editUrl }}'" style="cursor: pointer;">
                                        {{ $announcement->visible_until?->format('M d, Y') ?? '-' }}
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
                                                    <form action="{{ route('setup.announcements.destroy', $announcement->id) }}" method="POST"
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
                            @if($announcements->isEmpty())
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

                    @include('shared.pagination', ['paginator' => $announcements])
                </div>
            </div>
        </div>
    </div>
@endsection
