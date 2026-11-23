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

use App\Models\Article;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * How many matches each section lists. Sections report the real total and link to
     * the full list when there are more, so the counts never understate a large result
     * set.
     */
    private const PER_SECTION = 10;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sections = [];

        // A record code (C0042) opens that record when it exists and the user may view its module.
        if (preg_match('/^([a-z])(\d{4,})$/i', $q, $match)) {
            $codes = [
                Customer::CODE_PREFIX => [Customer::class, 'customers'],
                Project::CODE_PREFIX => [Project::class, 'projects'],
                Sale::CODE_PREFIX => [Sale::class, 'sales'],
                Contract::CODE_PREFIX => [Contract::class, 'contracts'],
                Invoice::CODE_PREFIX => [Invoice::class, 'invoices'],
                Task::CODE_PREFIX => [Task::class, 'tasks'],
            ];
            [$model, $module] = $codes[strtoupper($match[1])] ?? [null, null];

            if ($model && Auth::user()->hasPermission($module) && $model::whereKey((int) $match[2])->exists()) {
                return redirect()->route($module . '.show', (int) $match[2]);
            }
        }

        if (mb_strlen($q) >= 2) {
            $sections['customers'] = $this->section(
                Customer::where(function (Builder $query) use ($q) {
                    $query
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('company', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                }),
                route('customers', ['q' => $q])
            );

            $sections['projects'] = $this->section(
                Project::where(function (Builder $query) use ($q) {
                    $query
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                }),
                route('projects', ['q' => $q])
            );

            $sections['sales'] = $this->section(
                Sale::where(function (Builder $query) use ($q) {
                    $query
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                }),
                route('sales', ['q' => $q])
            );

            $sections['contracts'] = $this->section(
                Contract::where(function (Builder $query) use ($q) {
                    $query
                        ->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%");
                }),
                route('contracts', ['q' => $q])
            );

            if (Auth::user()->isAdmin()) {
                $sections['users'] = $this->section(
                    User::where(function (Builder $query) use ($q) {
                        $query
                            ->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    }),
                    route('setup.users', ['q' => $q])
                );
            }
        }

        $records = mb_strlen($q) >= 2 ? $this->records($q) : [];

        // ponytail: restricted users still run the hidden queries; skip them up front if search gets slow.
        $sections = array_filter(
            $sections,
            fn ($section, $module) => $module === 'users' || Auth::user()->hasPermission($module),
            ARRAY_FILTER_USE_BOTH
        );

        return view('pages.search', [
            'q' => $q,
            'sections' => array_filter($sections, fn (array $section) => $section['total'] > 0),
            'records' => array_filter($records, fn (array $record) => $record['section']['total'] > 0),
            'totalResults' => array_sum(array_column($sections, 'total'))
                + array_sum(array_map(fn (array $record) => $record['section']['total'], $records)),
        ]);
    }

    /**
     * The newer record types, each shown as a title, a muted line and its code. Only
     * the modules the user may view are queried.
     */
    private function records(string $q): array
    {
        $like = "%{$q}%";
        $customer = fn ($model) => $model->customer?->name;
        $definitions = [
            'contacts' => ['customers', 'person-lines-fill', Contact::with('customer')->where(fn ($query) => $query
                ->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->orWhere('email', 'like', $like)),
                fn (Contact $contact) => [route('customers.contacts.show', [$contact->customer_id, $contact->id]),
                    $contact->full_name, $contact->email ?: $contact->customer?->name, null]],
            'tasks' => ['tasks', 'check2-square', Task::with('customer')->where('title', 'like', $like),
                fn (Task $task) => [route('tasks.show', $task->id), $task->title, $customer($task), $task->code]],
            'tickets' => ['tickets', 'life-preserver', Ticket::with('customer')->where('subject', 'like', $like),
                fn (Ticket $ticket) => [route('tickets.show', $ticket->id), $ticket->subject, $customer($ticket), null]],
            'invoices' => ['invoices', 'receipt', Invoice::with('customer')->where(fn ($query) => $query
                ->where('number', 'like', $like)->orWhereHas('customer', fn ($query) => $query->where('name', 'like', $like))),
                fn (Invoice $invoice) => [route('invoices.show', $invoice->id), $invoice->number ?: __('draft'),
                    trim($customer($invoice) . ' · ' . format_currency($invoice->total, $invoice->currency), ' ·'), $invoice->code]],
            'estimates' => ['estimates', 'file-earmark-ruled', Estimate::with('customer')->where(fn ($query) => $query
                ->where('number', 'like', $like)->orWhereHas('customer', fn ($query) => $query->where('name', 'like', $like))),
                fn (Estimate $estimate) => [route('estimates.show', $estimate->id), $estimate->number ?: __('draft'),
                    $customer($estimate), null]],
            'proposals' => ['proposals', 'file-earmark-richtext', Proposal::with('customer')->where(fn ($query) => $query
                ->where('subject', 'like', $like)->orWhere('number', 'like', $like)),
                fn (Proposal $proposal) => [route('proposals.show', $proposal->id), $proposal->subject, $customer($proposal), null]],
            'articles' => ['articles', 'journal-text', Article::where(fn ($query) => $query->where('title', 'like', $like)->orWhere('category', 'like', $like)),
                fn (Article $article) => [route('articles.show', $article->id), $article->title, $article->category, null]],
        ];
        $records = [];

        foreach ($definitions as $key => [$module, $icon, $query, $row]) {
            if (!Auth::user()->hasPermission($module)) {
                continue;
            }

            $section = $this->section($query, route($key === 'contacts' ? 'customers' : $key, ['q' => $q]));
            $section['items'] = $section['items']->map(fn ($model) => array_combine(['url', 'title', 'meta', 'code'], $row($model)));
            $records[$key] = ['icon' => $icon, 'section' => $section];
        }

        return $records;
    }

    /**
     * Count the full result set, then read back only the first page of it.
     */
    private function section(Builder $query, string $url): array
    {
        $total = (clone $query)->count();

        return [
            'total' => $total,
            'items' => $query->limit(self::PER_SECTION)->get(),
            'url' => $url,
        ];
    }
}
