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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q', '');
        $results = [];

        if (strlen($q) >= 2) {
            $results['customers'] = Customer::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('company', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->limit(10)
                ->get();

            $results['projects'] = Project::where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->limit(10)
                ->get();

            $results['sales'] = Sale::where('name', 'like', "%{$q}%")
                ->orWhere('notes', 'like', "%{$q}%")
                ->limit(10)
                ->get();

            $results['contracts'] = Contract::where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orWhere('notes', 'like', "%{$q}%")
                ->limit(10)
                ->get();

            if (Auth::user()->isAdmin()) {
                $results['users'] = User::where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->limit(10)
                    ->get();
            }
        }

        $totalResults = collect($results)->flatten()->count();

        return view('pages.search', [
            'q' => $q,
            'results' => $results,
            'totalResults' => $totalResults,
        ]);
    }
}
