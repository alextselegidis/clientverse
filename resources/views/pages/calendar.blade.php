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
    {{ __('calendar') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('calendar')]
    ]])
@endsection

@php
    $start = $month->copy()->startOfWeek();
    $end = $month->copy()->endOfMonth()->endOfWeek();
    // type => [permission module, icon]; keep the icons in step with CalendarController.
    $types = array_filter([
        'tasks' => ['tasks', 'check2-square'],
        'milestones' => ['projects', 'flag'],
        'invoices' => ['invoices', 'receipt'],
        'contracts' => ['contracts', 'file-earmark-text'],
        'sales' => ['sales', 'graph-up'],
    ], fn ($entry) => auth()->user()->hasPermission($entry[0]));
@endphp

@section('content')
    <div class="card border-0 shadow-sm">
        {{-- One toolbar: month navigation on the left, search and type filter on the right --}}
        <div class="table-filters d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('calendar', array_filter(['month' => $month->copy()->subMonth()->format('Y-m'), 'type' => $type, 'q' => $q])) }}" class="btn btn-sm btn-light border"
                   aria-label="{{ __('previous_month') }}"><i class="bi bi-chevron-left"></i></a>
                <a href="{{ route('calendar', array_filter(['month' => $month->copy()->addMonth()->format('Y-m'), 'type' => $type, 'q' => $q])) }}" class="btn btn-sm btn-light border"
                   aria-label="{{ __('next_month') }}"><i class="bi bi-chevron-right"></i></a>
                <h5 class="mb-0 ms-2 text-nowrap">{{ $month->translatedFormat('F Y') }}</h5>
            </div>

            <form action="{{ route('calendar') }}" method="GET" class="d-flex flex-wrap gap-2 ms-md-auto">
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <div class="input-group" style="width: 16rem;">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0" value="{{ $q }}"
                           placeholder="{{ __('search') }}...">
                </div>
                <div style="width: 12rem;">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('type') }}: {{ __('all') }}</option>
                        @foreach($types as $key => $entry)
                            <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ __($key) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- A grid of weeks from lg up; below lg only days with events, as a list. --}}
        <div class="calendar-grid d-none d-lg-grid">
            @for($day = $start->copy(); $day->lte($start->copy()->addDays(6)); $day->addDay())
                <div class="calendar-weekday">{{ $day->translatedFormat('D') }}</div>
            @endfor
            @for($day = $start->copy(); $day->lte($end); $day->addDay())
                <div class="calendar-day {{ $day->month !== $month->month ? 'is-outside' : '' }} {{ $day->isToday() ? 'is-today' : '' }}">
                    <div class="calendar-date">{{ $day->day }}</div>
                    @foreach($events->get($day->toDateString(), []) as $event)
                        <a href="{{ $event['url'] }}" class="calendar-event text-truncate" title="{{ $event['label'] }}">
                            <i class="bi bi-{{ $event['icon'] }} me-1"></i>{{ $event['label'] }}
                        </a>
                    @endforeach
                </div>
            @endfor
        </div>

        <ul class="list-group list-group-flush d-lg-none">
            @forelse($events->sortKeys() as $date => $dayEvents)
                <li class="list-group-item">
                    <div class="small text-muted mb-1">{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('D, M j') }}</div>
                    @foreach($dayEvents as $event)
                        <a href="{{ $event['url'] }}" class="d-block text-truncate py-1">
                            <i class="bi bi-{{ $event['icon'] }} me-1"></i>{{ $event['label'] }}
                        </a>
                    @endforeach
                </li>
            @empty
                <li class="list-group-item text-muted">{{ __('no_records_found') }}</li>
            @endforelse
        </ul>

        <div class="card-footer bg-transparent d-flex flex-wrap gap-3 small text-muted">
            @foreach($types as $key => [$module, $icon])
                <span><i class="bi bi-{{ $icon }} me-1"></i>{{ __($key) }}</span>
            @endforeach
        </div>
    </div>
@endsection
