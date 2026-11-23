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

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\TaskRequest;
use App\Models\Task;

class TasksApiV1Controller extends ValidatedApiV1Controller
{
    protected $model = Task::class;

    protected $request = TaskRequest::class;

    public function filterableBy(): array
    {
        return ['title', 'status', 'priority', 'due_date', 'project_id', 'customer_id', 'assigned_to', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['title', 'description'];
    }

    public function includes(): array
    {
        return ['project', 'customer', 'assignedUser', 'timeEntries'];
    }
}
