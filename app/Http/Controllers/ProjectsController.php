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

use App\Enums\ProjectStatusEnum;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('customer');

        $q = $request->query('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $projects = $query->cursorPaginate(25);

        return view('pages.projects', [
            'projects' => $projects,
            'q' => $q,
            'status' => $status,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.projects-edit', [
            'project' => new Project(),
            'customers' => Customer::toOptions(),
            'users' => User::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $project = Project::create($request->all());

        if ($request->has('members')) {
            $project->members()->sync($request->input('members', []));
        }

        return redirect(route('projects.show', $project->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Project $project)
    {
        return view('pages.projects-show', [
            'project' => $project->load(['customer', 'milestones', 'members', 'contracts']),
        ]);
    }

    public function edit(Request $request, Project $project)
    {
        return view('pages.projects-edit', [
            'project' => $project->load('members'),
            'customers' => Customer::toOptions(),
            'users' => User::all(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|min:2',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $project->fill($request->all());
        $project->save();

        if ($request->has('members')) {
            $project->members()->sync($request->input('members', []));
        }

        return redirect(route('projects.show', $project->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Project $project)
    {
        $project->delete();

        return redirect(route('projects'))->with('success', __('record_deleted_message'));
    }
}
