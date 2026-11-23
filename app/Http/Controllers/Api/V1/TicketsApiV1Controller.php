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

use App\Http\Requests\Api\TicketRequest;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Orion\Http\Requests\Request;

class TicketsApiV1Controller extends ValidatedApiV1Controller
{
    protected $model = Ticket::class;

    protected $request = TicketRequest::class;

    public function filterableBy(): array
    {
        return ['subject', 'status', 'priority', 'customer_id', 'contact_id', 'department_id', 'assigned_to', 'created_at', 'updated_at'];
    }

    public function sortableBy(): array
    {
        return ['subject', 'status', 'priority', 'created_at', 'updated_at'];
    }

    public function searchableBy(): array
    {
        return ['subject'];
    }

    public function includes(): array
    {
        return ['customer', 'contact', 'department', 'assignedUser', 'replies'];
    }

    /**
     * The message sent with a new ticket becomes its first reply, written as the token's user.
     */
    protected function afterStore(Request $request, Model $entity)
    {
        $entity->replies()->create(['user_id' => $request->user()->id, 'body' => $request->validated('body')]);
    }
}
