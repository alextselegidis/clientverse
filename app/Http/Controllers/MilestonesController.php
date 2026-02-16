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

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestonesController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $query = $project->milestones();

        $q = $request->query('q');
        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        $sort = $request->query('sort', 'due_date');
        $direction = $request->query('direction', 'asc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $milestones = $query->paginate(25);

        return view('pages.milestones', [
            'project' => $project,
            'milestones' => $milestones,
            'q' => $q,
        ]);
    }

    public function create(Request $request, Project $project)
    {
        return view('pages.milestones-edit', [
            'project' => $project,
            'milestone' => new Milestone(),
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $data = $request->all();
        $data['project_id'] = $project->id;
        $data['is_completed'] = $request->has('is_completed');

        $milestone = Milestone::create($data);

        return redirect(route('projects.milestones.show', [$project->id, $milestone->id]))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Project $project, Milestone $milestone)
    {
        return view('pages.milestones-show', [
            'project' => $project,
            'milestone' => $milestone,
        ]);
    }

    public function edit(Request $request, Project $project, Milestone $milestone)
    {
        return view('pages.milestones-edit', [
            'project' => $project,
            'milestone' => $milestone,
        ]);
    }

    public function update(Request $request, Project $project, Milestone $milestone)
    {
        $request->validate([
            'name' => 'required|min:2',
        ]);

        $data = $request->all();
        $data['is_completed'] = $request->has('is_completed');

        $milestone->fill($data);
        $milestone->save();

        return redirect(route('projects.milestones.edit', [$project->id, $milestone->id]))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Project $project, Milestone $milestone)
    {
        $milestone->delete();

        return redirect(route('projects.milestones', $project->id))->with('success', __('record_deleted_message'));
    }
}
