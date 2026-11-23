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
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('customer', 'assignedUser', 'department');

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('subject', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$q}%"));
            });
        }

        $status = $request->query('status');

        if ($status) {
            $query->where('status', $status);
        }

        $departmentId = $request->query('department_id');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        sort_query($query, $request->query('sort', 'updated_at'), $request->query('direction', 'desc'));

        return view('pages.tickets', [
            'tickets' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
            'departmentId' => $departmentId,
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.tickets-create', [
            'ticket' => new Ticket(['customer_id' => $request->query('customer_id'), 'priority' => 'normal']),
            'customers' => Customer::toOptions(),
            'users' => $this->users(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'priority' => ['required', Rule::in(array_keys(Task::priorities()))],
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
            'body' => 'required|string',
        ]);

        $ticket = Ticket::create($data);
        $ticket->replies()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        return redirect(route('tickets.show', $ticket->id))->with('success', __('record_saved_message'));
    }

    public function show(Ticket $ticket)
    {
        return view('pages.tickets-show', [
            'ticket' => $ticket->load('customer', 'contact', 'department', 'assignedUser', 'replies.user', 'replies.contact'),
            'users' => $this->users(),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $ticket->update($request->validate([
            'status' => ['required', Rule::in(array_keys(Ticket::statuses()))],
            'priority' => ['required', Rule::in(array_keys(Task::priorities()))],
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]));

        return redirect(route('tickets.show', $ticket->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect(route('tickets'))->with('success', __('record_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tickets,id',
        ]);

        Ticket::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('tickets'))->with('success', __('records_deleted_message'));
    }

    private function users()
    {
        return User::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }
}
