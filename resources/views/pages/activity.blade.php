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
    {{ __('activity_log') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup'), 'url' => route('setup.localization')],
        ['label' => __('activity_log')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <div class="flex-grow-1">
            <div class="card card-table border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="table-filters">
                        <form action="{{ route('setup.activity') }}" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <select name="user_id" class="form-select" onchange="this.form.submit()" aria-label="{{ __('user') }}">
                                    <option value="">{{ __('user') }}: {{ __('all') }}</option>
                                    @foreach($users as $id => $name)
                                        <option value="{{ $id }}" @selected($userId == $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="type" class="form-select" onchange="this.form.submit()" aria-label="{{ __('type') }}">
                                    <option value="">{{ __('type') }}: {{ __('all') }}</option>
                                    @foreach(\App\Models\Activity::subjectTypes() as $class => $label)
                                        <option value="{{ $class }}" @selected($type === $class)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-light-header align-middle mb-0">
                            <thead>
                            <tr>
                                <th class="ps-3 w-100 w-md-auto">{{ __('record') }}</th>
                                <th class="text-nowrap">{{ __('action') }}</th>
                                <th class="d-none d-md-table-cell text-nowrap">{{ __('user') }}</th>
                                <th class="d-none d-lg-table-cell">{{ __('changes') }}</th>
                                <th class="pe-3 d-none d-lg-table-cell text-nowrap">{{ __('date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td class="ps-3" style="width: 32%;">
                                        <div class="text-truncate" style="width: 0; min-width: 100%;">
                                            <span class="text-muted">{{ $activity->subject_name }}</span>
                                            <span class="fw-medium">{{ $activity->label }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ \App\Models\Activity::eventColors()[$activity->event] ?? 'secondary' }}">{{ __($activity->event) }}</span>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ $activity->user?->name ?? __('system') }}</td>
                                    <td class="d-none d-lg-table-cell" style="width: 22%;">
                                        @if($activity->changes)
                                            <div class="small text-muted text-truncate" style="width: 0; min-width: 100%;"
                                                 title="{{ collect($activity->changes)->map(fn ($pair, $key) => $key . ': ' . $pair[0] . ' → ' . $pair[1])->implode(PHP_EOL) }}">
                                                {{ collect($activity->changes)->keys()->map(fn ($key) => __($key))->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="pe-3 d-none d-lg-table-cell text-nowrap" title="{{ $activity->created_at->format('M d, Y H:i') }}">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                            @if($activities->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                        {{ __('no_records_found') }}
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                    @include('shared.pagination', ['paginator' => $activities])
                </div>
            </div>
        </div>
    </div>
@endsection
