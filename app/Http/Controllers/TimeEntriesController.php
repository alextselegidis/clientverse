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

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class TimeEntriesController extends Controller
{
    /**
     * Start a timer on the task. A person runs one timer at a time, so any
     * other running timer of theirs is stopped first.
     */
    public function start(Request $request, Task $task)
    {
        TimeEntry::running()->where('user_id', $request->user()->id)->update(['ended_at' => now()]);

        $task->timeEntries()->create(['user_id' => $request->user()->id, 'started_at' => now()]);

        return redirect(route('tasks.show', $task->id));
    }

    public function stop(Request $request, Task $task)
    {
        $task->timeEntries()->running()->where('user_id', $request->user()->id)->update(['ended_at' => now()]);

        return redirect(route('tasks.show', $task->id));
    }

    public function store(Request $request, Task $task)
    {
        $data = $request->validate([
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'note' => 'nullable|string|max:255',
        ]);

        $task->timeEntries()->create($data + ['user_id' => $request->user()->id]);

        return redirect(route('tasks.show', $task->id))->with('success', __('record_saved_message'));
    }

    // The route uses scopeBindings(), so an entry of another task is a 404 here.
    public function destroy(Task $task, TimeEntry $timeEntry)
    {
        $timeEntry->delete();

        return redirect(route('tasks.show', $task->id))->with('success', __('record_deleted_message'));
    }
}
