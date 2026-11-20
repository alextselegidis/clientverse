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
    {{ $survey->title }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('surveys'), 'url' => route('surveys')],
        ['label' => $survey->title]
    ]])
@endsection

@section('navActions')
    <a href="{{ route('surveys.edit', $survey->id) }}" class="nav-link me-lg-3">
        <i class="bi bi-pencil me-2"></i>
        {{ __('edit') }}
    </a>
    <form action="{{ route('surveys.destroy', $survey->id) }}" method="POST"
          onsubmit="return confirm('{{ __('delete_record_prompt') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="nav-link">
            <i class="bi bi-trash me-2"></i>
            {{ __('delete') }}
        </button>
    </form>
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            @foreach($survey->results() as $result)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent"><h6 class="mb-0">{{ $result['text'] }}</h6></div>
                    <div class="card-body">
                        @if($result['type'] === 'choice')
                            @php($max = max(1, max($result['counts'] ?: [0])))
                            @foreach($result['counts'] as $option => $count)
                                <div class="d-flex justify-content-between small"><span>{{ $option }}</span><span>{{ $count }}</span></div>
                                <div class="progress mb-2" style="height: 6px;" role="presentation">
                                    <div class="progress-bar" style="width: {{ round($count / $max * 100) }}%"></div>
                                </div>
                            @endforeach
                        @elseif($result['type'] === 'rating')
                            <span class="h4">{{ $result['average'] ?? '-' }}</span> <span class="text-muted">/ 5 · {{ trans_choice('response_count', $result['count']) }}</span>
                        @else
                            @forelse($result['texts'] as $text)
                                <p class="border-start ps-3 mb-2">{!! nl2br(e($text)) !!}</p>
                            @empty
                                <p class="text-muted mb-0">{{ __('no_records_found') }}</p>
                            @endforelse
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="mb-3"><strong>{{ __('responses') }}:</strong> {{ $survey->responses->count() }}</p>
                    <label for="public-url" class="form-label small text-muted">{{ __('survey_link_hint') }}</label>
                    <input type="text" id="public-url" class="form-control form-control-sm" readonly
                           value="{{ $survey->publicUrl() }}" onclick="this.select()">
                </div>
            </div>
        </div>
    </div>
@endsection
