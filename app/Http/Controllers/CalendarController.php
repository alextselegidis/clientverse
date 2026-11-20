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

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Milestone;
use App\Models\Sale;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * One month of dates from across the CRM. Nothing is stored here: each event is a
 * record's own due or end date, shown only for modules the user may view.
 */
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);

        $month = Carbon::createFromFormat('Y-m-d', ($request->query('month') ?? today()->format('Y-m')) . '-01')->startOfDay();
        $range = [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()];
        $user = $request->user();
        $events = collect();

        if ($user->hasPermission('tasks')) {
            Task::where('status', '!=', 'done')->whereBetween('due_date', $range)->get()
                ->each(fn (Task $task) => $events->push([
                    'date' => $task->due_date, 'label' => $task->title, 'icon' => 'check2-square',
                    'url' => route('tasks.show', $task->id),
                ]));
        }

        if ($user->hasPermission('projects')) {
            Milestone::whereBetween('due_date', $range)->get()
                ->each(fn (Milestone $milestone) => $events->push([
                    'date' => $milestone->due_date, 'label' => $milestone->name, 'icon' => 'flag',
                    'url' => route('projects.milestones.show', [$milestone->project_id, $milestone->id]),
                ]));
        }

        if ($user->hasPermission('invoices')) {
            Invoice::where('status', 'sent')->whereColumn('amount_paid', '<', 'total')->whereBetween('due_date', $range)->get()
                ->each(fn (Invoice $invoice) => $events->push([
                    'date' => $invoice->due_date, 'label' => $invoice->number, 'icon' => 'receipt',
                    'url' => route('invoices.show', $invoice->id),
                ]));
        }

        if ($user->hasPermission('contracts')) {
            Contract::whereBetween('end_date', $range)->get()
                ->each(fn (Contract $contract) => $events->push([
                    'date' => $contract->end_date, 'label' => $contract->title, 'icon' => 'file-earmark-text',
                    'url' => route('contracts.show', $contract->id),
                ]));
        }

        if ($user->hasPermission('sales')) {
            Sale::whereNotIn('stage', ['won', 'lost'])->whereBetween('expected_close_date', $range)->get()
                ->each(fn (Sale $sale) => $events->push([
                    'date' => $sale->expected_close_date, 'label' => $sale->name, 'icon' => 'graph-up',
                    'url' => route('sales.show', $sale->id),
                ]));
        }

        return view('pages.calendar', [
            'month' => $month,
            'events' => $events->groupBy(fn ($event) => $event['date']->toDateString()),
        ]);
    }
}
