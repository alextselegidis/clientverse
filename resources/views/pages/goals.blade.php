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
    {{ __('goals') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('reports'), 'url' => route('reports')],
        ['label' => __('goals')]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('goals.create') }}" class="nav-link">
        <i class="bi bi-plus-square me-2"></i>
        {{ __('create') }}
    </a>
@endsection

@section('content')
    <div class="row g-4">
        @forelse($goals as $goal)
            @php($progress = $goal->progress)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <a href="{{ route('goals.edit', $goal->id) }}" class="fw-medium text-truncate">{{ $goal->name }}</a>
                            @if($goal->end_date->lt(today()))
                                <span class="badge bg-{{ $goal->percent >= 100 ? 'success' : 'secondary' }}">{{ __('ended') }}</span>
                            @endif
                        </div>
                        <div class="small text-muted mb-3">
                            {{ \App\Models\Goal::metrics()[$goal->metric] }} ·
                            {{ $goal->start_date->format('M d') }} – {{ $goal->end_date->format('M d, Y') }}
                        </div>
                        <div class="progress mb-2" style="height: 8px;" role="progressbar"
                             aria-valuenow="{{ $goal->percent }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $goal->name }}">
                            <div class="progress-bar {{ $goal->percent >= 100 ? 'bg-success' : '' }}" style="width: {{ $goal->percent }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>{{ $goal->format($progress) }} / {{ $goal->format((float) $goal->target) }}</span>
                            <span class="fw-medium">{{ $goal->percent }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <i class="bi bi-bullseye display-4 d-block mb-3"></i>
                {{ __('no_records_found') }}
            </div>
        @endforelse
    </div>
@endsection
