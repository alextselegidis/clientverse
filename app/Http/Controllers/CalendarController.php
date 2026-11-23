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
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'type' => 'nullable|in:tasks,milestones,invoices,contracts,sales',
            'q' => 'nullable|string|max:255',
        ]);

        $month = Carbon::createFromFormat('Y-m-d', ($request->query('month') ?? today()->format('Y-m')) . '-01')->startOfDay();
        $range = [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()];
        $user = $request->user();
        $events = collect();

        if ($user->hasPermission('tasks')) {
            Task::where('status', '!=', 'done')->whereBetween('due_date', $range)->get()
                ->each(fn (Task $task) => $events->push([
                    'date' => $task->due_date, 'label' => $task->title, 'icon' => 'check2-square', 'type' => 'tasks',
                    'url' => route('tasks.show', $task->id),
                ]));
        }

        if ($user->hasPermission('projects')) {
            Milestone::whereBetween('due_date', $range)->get()
                ->each(fn (Milestone $milestone) => $events->push([
                    'date' => $milestone->due_date, 'label' => $milestone->name, 'icon' => 'flag', 'type' => 'milestones',
                    'url' => route('projects.milestones.show', [$milestone->project_id, $milestone->id]),
                ]));
        }

        if ($user->hasPermission('invoices')) {
            Invoice::where('status', 'sent')->whereColumn('amount_paid', '<', 'total')->whereBetween('due_date', $range)->get()
                ->each(fn (Invoice $invoice) => $events->push([
                    'date' => $invoice->due_date, 'label' => $invoice->number, 'icon' => 'receipt', 'type' => 'invoices',
                    'url' => route('invoices.show', $invoice->id),
                ]));
        }

        if ($user->hasPermission('contracts')) {
            Contract::whereBetween('end_date', $range)->get()
                ->each(fn (Contract $contract) => $events->push([
                    'date' => $contract->end_date, 'label' => $contract->title, 'icon' => 'file-earmark-text', 'type' => 'contracts',
                    'url' => route('contracts.show', $contract->id),
                ]));
        }

        if ($user->hasPermission('sales')) {
            Sale::whereNotIn('stage', ['won', 'lost'])->whereBetween('expected_close_date', $range)->get()
                ->each(fn (Sale $sale) => $events->push([
                    'date' => $sale->expected_close_date, 'label' => $sale->name, 'icon' => 'graph-up', 'type' => 'sales',
                    'url' => route('sales.show', $sale->id),
                ]));
        }

        // ponytail: filters one month in memory; push type and q into the queries if a month gets large.
        $type = $request->query('type');
        $q = trim((string) $request->query('q'));
        $events = $events
            ->when($type, fn ($events) => $events->where('type', $type))
            ->when($q !== '', fn ($events) => $events->filter(fn ($event) => mb_stripos($event['label'], $q) !== false));

        return view('pages.calendar', [
            'month' => $month,
            'type' => $type,
            'q' => $q,
            'events' => $events->groupBy(fn ($event) => $event['date']->toDateString()),
        ]);
    }
}
