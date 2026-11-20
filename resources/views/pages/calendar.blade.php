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
@endphp

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <a href="{{ route('calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-sm btn-light border"
               aria-label="{{ __('previous_month') }}"><i class="bi bi-chevron-left"></i></a>
            <h5 class="mb-0">{{ $month->translatedFormat('F Y') }}</h5>
            <a href="{{ route('calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-sm btn-light border"
               aria-label="{{ __('next_month') }}"><i class="bi bi-chevron-right"></i></a>
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
    </div>
@endsection
