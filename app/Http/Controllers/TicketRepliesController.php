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

use App\Models\Ticket;
use App\Notifications\TicketReplied;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Throwable;

class TicketRepliesController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'body' => 'required|string',
            'notify' => 'boolean',
        ]);

        $reply = $ticket->replies()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        if ($ticket->status !== 'closed') {
            $ticket->update(['status' => 'answered']);
        }

        if ($request->boolean('notify') && $ticket->recipient_email) {
            try {
                Notification::route('mail', $ticket->recipient_email)->notify(new TicketReplied($reply));
            } catch (Throwable $exception) {
                // The reply is saved either way; say so rather than showing an error page.
                report($exception);

                return redirect(route('tickets.show', $ticket->id))->with('error', __('reply_saved_email_failed_message'));
            }
        }

        return redirect(route('tickets.show', $ticket->id))->with('success', __('record_saved_message'));
    }
}
