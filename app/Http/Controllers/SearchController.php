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
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Sale;
use App\Models\Task;
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

        // ponytail: restricted users still run the hidden queries; skip them up front if search gets slow.
        $sections = array_filter(
            $sections,
            fn ($section, $module) => $module === 'users' || Auth::user()->hasPermission($module),
            ARRAY_FILTER_USE_BOTH
        );

        return view('pages.search', [
            'q' => $q,
            'sections' => array_filter($sections, fn (array $section) => $section['total'] > 0),
            'totalResults' => array_sum(array_column($sections, 'total')),
        ]);
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
