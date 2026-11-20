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

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SurveysController extends Controller
{
    public function index()
    {
        return view('pages.surveys', ['surveys' => Survey::withCount('responses')->latest()->paginate(25)]);
    }

    public function create()
    {
        return view('pages.surveys-edit', ['survey' => new Survey(['is_open' => true, 'questions' => []])]);
    }

    public function store(Request $request)
    {
        $survey = Survey::create($this->validateSurvey($request));

        return redirect(route('surveys.show', $survey->id))->with('success', __('record_saved_message'));
    }

    public function show(Survey $survey)
    {
        return view('pages.surveys-show', ['survey' => $survey->load('responses')]);
    }

    public function edit(Survey $survey)
    {
        return view('pages.surveys-edit', ['survey' => $survey]);
    }

    public function update(Request $request, Survey $survey)
    {
        $survey->update($this->validateSurvey($request));

        return redirect(route('surveys.show', $survey->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Survey $survey)
    {
        $survey->delete();

        return redirect(route('surveys'))->with('success', __('record_deleted_message'));
    }

    /**
     * Questions arrive as rows; a row without text is dropped, and choice
     * options are typed one per line.
     */
    private function validateSurvey(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_open' => 'boolean',
            'questions' => 'array',
            'questions.*.text' => 'nullable|string|max:500',
            'questions.*.type' => ['required', Rule::in(Survey::TYPES)],
            'questions.*.options' => 'nullable|string',
        ]);

        $data['questions'] = collect($data['questions'] ?? [])
            ->filter(fn ($question) => filled($question['text'] ?? null))
            ->map(fn ($question) => [
                'text' => $question['text'],
                'type' => $question['type'],
                'options' => $question['type'] === 'choice'
                    ? array_values(array_filter(array_map('trim', preg_split('/\R/', $question['options'] ?? ''))))
                    : [],
            ])
            ->values()
            ->all();

        return $data;
    }
}
