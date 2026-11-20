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

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoalsController extends Controller
{
    public function index()
    {
        return view('pages.goals', ['goals' => Goal::orderByDesc('end_date')->get()]);
    }

    public function create()
    {
        return view('pages.goals-edit', ['goal' => new Goal([
            'metric' => 'invoiced',
            'currency' => default_currency(),
            'start_date' => today()->startOfMonth(),
            'end_date' => today()->endOfMonth(),
        ])]);
    }

    public function store(Request $request)
    {
        Goal::create($this->validateGoal($request));

        return redirect(route('goals'))->with('success', __('record_saved_message'));
    }

    public function edit(Goal $goal)
    {
        return view('pages.goals-edit', ['goal' => $goal]);
    }

    public function update(Request $request, Goal $goal)
    {
        $goal->update($this->validateGoal($request));

        return redirect(route('goals'))->with('success', __('record_saved_message'));
    }

    public function destroy(Goal $goal)
    {
        $goal->delete();

        return redirect(route('goals'))->with('success', __('record_deleted_message'));
    }

    private function validateGoal(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'metric' => ['required', Rule::in(array_keys(Goal::metrics()))],
            'currency' => 'nullable|required_if:metric,invoiced,collected|string|size:3',
            'target' => 'required|numeric|gt:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    }
}
