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
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('shared.head')
    <meta name="robots" content="noindex">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 720px;">
    <div class="bg-white shadow-sm rounded-3 p-4 p-md-5">
        <h1 class="h3 mb-2">{{ $survey->title }}</h1>
        @if($survey->description)
            <p class="text-muted">{!! nl2br(e($survey->description)) !!}</p>
        @endif

        @if(session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @elseif(!$survey->is_open)
            <p class="text-muted mb-0">{{ __('survey_closed_message') }}</p>
        @else
            <form action="{{ request()->fullUrl() }}" method="POST" class="mt-4">
                @csrf
                @foreach($survey->questions as $i => $question)
                    <fieldset class="mb-4">
                        <legend class="fs-6 fw-medium">{{ $question['text'] }}</legend>
                        @if($question['type'] === 'choice')
                            @foreach($question['options'] as $j => $option)
                                <div class="form-check">
                                    <input type="radio" id="q{{ $i }}-{{ $j }}" name="answers[{{ $i }}]" value="{{ $option }}" class="form-check-input"
                                           @checked(old("answers.$i") === $option)>
                                    <label for="q{{ $i }}-{{ $j }}" class="form-check-label">{{ $option }}</label>
                                </div>
                            @endforeach
                        @elseif($question['type'] === 'rating')
                            <div class="btn-group" role="group">
                                @for($n = 1; $n <= 5; $n++)
                                    <input type="radio" class="btn-check" id="q{{ $i }}-{{ $n }}" name="answers[{{ $i }}]" value="{{ $n }}"
                                           @checked(old("answers.$i") == $n)>
                                    <label class="btn btn-light border" for="q{{ $i }}-{{ $n }}">{{ $n }}</label>
                                @endfor
                            </div>
                        @else
                            <textarea name="answers[{{ $i }}]" class="form-control" rows="3" aria-label="{{ $question['text'] }}">{{ old("answers.$i") }}</textarea>
                        @endif
                    </fieldset>
                @endforeach
                @include('shared.errors', ['class' => 'mb-3'])
                <button type="submit" class="btn btn-primary">{{ __('submit') }}</button>
            </form>
        @endif
    </div>
</div>
</body>
</html>
