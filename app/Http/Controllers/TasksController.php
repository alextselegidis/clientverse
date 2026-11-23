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

use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TasksController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('project', 'assignedUser');

        $q = $request->query('q');

        if ($q) {
            $query->where('title', 'like', "%{$q}%");
        }

        $status = $request->query('status');

        if ($status) {
            $query->where('status', $status);
        }

        $mine = $request->boolean('mine');

        if ($mine) {
            $query->where('assigned_to', $request->user()->id);
        }

        sort_query($query, $request->query('sort', 'due_date'), $request->query('direction', 'asc'));

        return view('pages.tasks', [
            'tasks' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
            'mine' => $mine,
        ]);
    }

    public function create(Request $request)
    {
        $task = new Task([
            'project_id' => $request->query('project_id'),
            'customer_id' => $request->query('customer_id'),
            'assigned_to' => $request->user()->id,
        ]);

        return view('pages.tasks-edit', ['task' => $task] + $this->options());
    }

    public function store(Request $request)
    {
        $task = Task::create($this->validateTask($request));

        return redirect(route('tasks.show', $task->id))->with('success', __('record_saved_message'));
    }

    public function show(Task $task)
    {
        return view('pages.tasks-show', [
            'task' => $task->load('project', 'customer', 'assignedUser', 'timeEntries.user'),
        ]);
    }

    public function edit(Task $task)
    {
        return view('pages.tasks-edit', ['task' => $task] + $this->options());
    }

    public function update(Request $request, Task $task)
    {
        $task->update($this->validateTask($request));

        return redirect(route('tasks.show', $task->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect(route('tasks'))->with('success', __('record_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tasks,id',
        ]);

        Task::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('tasks'))->with('success', __('records_deleted_message'));
    }

    private function options(): array
    {
        return [
            'projects' => Project::toOptions(),
            'customers' => Customer::toOptions(),
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validateTask(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(array_keys(Task::statuses()))],
            'priority' => ['required', Rule::in(array_keys(Task::priorities()))],
            'due_date' => 'nullable|date',
            'project_id' => 'nullable|exists:projects,id',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
    }
}
