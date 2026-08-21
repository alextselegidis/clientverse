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
use App\Models\Project;
use App\Models\Sale;
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
