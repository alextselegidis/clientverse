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
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Overview widgets
        $totalCustomers = Customer::count();
        $activeProjects = Project::where('status', 'active')->count();
        $openDeals = Sale::whereNotIn('stage', ['won', 'lost'])->count();
        $openDealsValue = Sale::whereNotIn('stage', ['won', 'lost'])->sum('value');
        $activeContracts = Contract::where('status', 'active')->count();

        // Recent activity
        $recentCustomers = Customer::orderBy('created_at', 'desc')->limit(5)->get();
        $recentProjects = Project::with('customer')->orderBy('updated_at', 'desc')->limit(5)->get();
        $recentSales = Sale::with('customer')->orderBy('updated_at', 'desc')->limit(5)->get();

        // Performance metrics
        $monthlySales = Sale::where('stage', 'won')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('value');

        $completedProjects = Project::where('status', 'completed')->count();
        $totalProjects = Project::count();
        $projectCompletionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0;

        return view('pages.dashboard', [
            'totalCustomers' => $totalCustomers,
            'activeProjects' => $activeProjects,
            'openDeals' => $openDeals,
            'openDealsValue' => $openDealsValue,
            'activeContracts' => $activeContracts,
            'recentCustomers' => $recentCustomers,
            'recentProjects' => $recentProjects,
            'recentSales' => $recentSales,
            'monthlySales' => $monthlySales,
            'projectCompletionRate' => $projectCompletionRate,
        ]);
    }
}
