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
    {{ $survey->exists ? $survey->title : __('create') . ' ' . __('survey') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('surveys'), 'url' => route('surveys')],
        // An existing record links back to its page, so the trail leads out of the form.
        ...($survey->exists
            ? [['label' => $survey->title, 'url' => route('surveys.show', $survey->id)], ['label' => __('edit')]]
            : [['label' => __('create')]]),
    ]])
@endsection

@php
    $questions = array_values(old('questions', $survey->questions ?? []));
    $questions[] = ['text' => '', 'type' => 'text', 'options' => ''];
@endphp

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ $survey->exists ? route('surveys.update', $survey->id) : route('surveys.store') }}" method="POST" id="edit-form">
                @csrf
                @if($survey->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('title') }} <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required autofocus maxlength="255"
                           value="{{ old('title', $survey->title) }}">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('description') }}</label>
                    <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $survey->description) }}</textarea>
                </div>
                <div class="form-check mb-4">
                    <input type="hidden" name="is_open" value="0">
                    <input type="checkbox" id="is_open" name="is_open" value="1" class="form-check-input" @checked(old('is_open', $survey->is_open))>
                    <label for="is_open" class="form-check-label">{{ __('accepting_responses') }}</label>
                </div>

                @if($locked)
                    <h6 class="mb-1">{{ __('questions') }}</h6>
                    <p class="form-text mt-0">{{ __('questions_locked_hint') }}</p>
                    <ol class="mb-0">
                        @foreach($survey->questions as $question)
                            <li>{{ $question['text'] }} <span class="text-muted small">({{ __('question_type_' . $question['type']) }})</span></li>
                        @endforeach
                    </ol>
                @else
                    <h6 class="mb-1">{{ __('questions') }}</h6>
                    <p class="form-text mt-0">{{ __('questions_hint') }}</p>
                    <div class="table-responsive mb-2">
                        <table class="table table-light-header align-middle mb-0">
                            <thead>
                            <tr>
                                <th style="min-width: 240px;">{{ __('question') }}</th>
                                <th style="width: 150px;">{{ __('type') }}</th>
                                <th style="min-width: 200px;">{{ __('options') }}</th>
                            </tr>
                            </thead>
                            <tbody id="survey-questions">
                            @foreach($questions as $i => $question)
                                @include('shared.survey-question', ['index' => $i, 'question' => $question])
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <template id="survey-question-template">
                        @include('shared.survey-question', ['index' => '__INDEX__', 'question' => []])
                    </template>
                    <button type="button" class="btn btn-sm btn-light border" data-add-row="survey-question-template" data-target="survey-questions">
                        <i class="bi bi-plus me-1"></i>{{ __('add_question') }}
                    </button>
                @endif

                @include('shared.errors', ['class' => 'mt-3 mb-0'])
            </form>
        </div>
        <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
            <button type="submit" form="edit-form" class="btn btn-primary">{{ __('save') }}</button>
        </div>
    </div>
@endsection
