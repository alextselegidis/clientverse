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

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * The client portal: one page listing everything a customer may see, linking
 * each document to its existing signed public page (where it is viewed, paid,
 * accepted or signed), plus the customer's support tickets.
 */
class PortalController extends Controller
{
    public function index(Request $request)
    {
        $customerId = $request->user('contact')->customer_id;
        $sent = fn ($model) => $model::where('customer_id', $customerId)->where('status', '!=', 'draft')
            ->latest('issue_date')->latest('id')->get();

        return view('pages.portal', [
            'invoices' => $sent(Invoice::class),
            'estimates' => $sent(Estimate::class),
            'proposals' => $sent(Proposal::class),
            'contracts' => Contract::where('customer_id', $customerId)->latest('start_date')->latest('id')->get(),
            'projects' => Project::where('customer_id', $customerId)->where('visibility', 'shared')->latest()->get(),
            'tickets' => Ticket::where('customer_id', $customerId)->latest('updated_at')->get(),
            'announcements' => Announcement::shownTo('customers')->get(),
        ]);
    }

    public function createTicket()
    {
        return view('pages.portal-ticket', ['ticket' => null]);
    }

    public function storeTicket(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'body' => 'required|string|max:20000',
        ]);

        $contact = $request->user('contact');

        $ticket = Ticket::create([
            'customer_id' => $contact->customer_id,
            'contact_id' => $contact->id,
            'department_id' => $data['department_id'] ?? null,
            'subject' => $data['subject'],
            'status' => 'open',
            'priority' => 'normal',
        ]);
        $ticket->replies()->create(['contact_id' => $contact->id, 'body' => $data['body']]);

        return redirect(route('portal.tickets.show', $ticket->id))->with('success', __('ticket_submitted_message'));
    }

    public function showTicket(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        return view('pages.portal-ticket', ['ticket' => $ticket->load('replies.user', 'replies.contact')]);
    }

    public function replyToTicket(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        $data = $request->validate(['body' => 'required|string|max:20000']);

        $ticket->replies()->create(['contact_id' => $request->user('contact')->id, 'body' => $data['body']]);
        $ticket->update(['status' => 'open']);

        return redirect(route('portal.tickets.show', $ticket->id))->with('success', __('record_saved_message'));
    }

    // Another customer's ticket is a 404, not a 403, so ids cannot be probed.
    private function authorizeTicket(Request $request, Ticket $ticket): void
    {
        abort_unless((int) $ticket->customer_id === (int) $request->user('contact')->customer_id, 404);
    }
}
