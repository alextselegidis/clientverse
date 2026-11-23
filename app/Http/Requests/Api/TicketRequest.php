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

namespace App\Http\Requests\Api;

use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Validation\Rule;

/**
 * A new ticket takes its first message as "body"; afterwards only the handling
 * fields change, as on the web page.
 */
class TicketRequest extends ApiRequest
{
    protected function fields(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'priority' => ['required', Rule::in(array_keys(Task::priorities()))],
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'body' => 'required|string',
        ];
    }

    public function updateRules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(array_keys(Ticket::statuses()))],
            'priority' => ['sometimes', Rule::in(array_keys(Task::priorities()))],
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
