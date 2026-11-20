<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicSurveysController extends Controller
{
    public function show(Survey $survey)
    {
        return view('pages.public-survey', ['survey' => $survey]);
    }

    public function respond(Request $request, Survey $survey)
    {
        abort_unless($survey->is_open, 404);

        $rules = ['answers' => 'array'];

        foreach ($survey->questions as $index => $question) {
            $rules["answers.{$index}"] = match ($question['type']) {
                'choice' => ['nullable', Rule::in($question['options'])],
                'rating' => 'nullable|integer|between:1,5',
                default => 'nullable|string|max:5000',
            };
        }

        $answers = $request->validate($rules)['answers'] ?? [];

        // Keep only answers to questions that exist, keyed by question index.
        $survey->responses()->create(['answers' => array_intersect_key($answers, $survey->questions)]);

        return back()->with('success', __('survey_thanks_message'));
    }
}
