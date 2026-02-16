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

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('customer');

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $stage = $request->query('stage');
        if ($stage) {
            $query->where('stage', $stage);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $sales = $query->cursorPaginate(25);

        return view('pages.sales', [
            'sales' => $sales,
            'q' => $q,
            'stage' => $stage,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.sales-edit', [
            'sale' => new Sale(),
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $sale = Sale::create($request->all());

        return redirect(route('sales.edit', ['sale' => $sale->id]));
    }

    public function show(Request $request, Sale $sale)
    {
        return view('pages.sales-show', [
            'sale' => $sale->load('customer'),
        ]);
    }

    public function edit(Request $request, Sale $sale)
    {
        return view('pages.sales-edit', [
            'sale' => $sale,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'name' => 'required|min:2',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $sale->fill($request->input());
        $sale->save();

        return redirect(route('sales.edit', $sale->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Sale $sale)
    {
        $sale->delete();

        return redirect()->back()->with('success', __('record_deleted_message'));
    }

    public function convertToContract(Request $request, Sale $sale)
    {
        $contract = Contract::create([
            'customer_id' => $sale->customer_id,
            'name' => $sale->name,
            'value' => $sale->value,
            'currency' => $sale->currency,
            'status' => 'draft',
            'notes' => $sale->notes,
        ]);

        $sale->update(['stage' => 'won']);

        return redirect(route('contracts.edit', $contract->id))->with('success', __('record_saved_message'));
    }

    public function convertToProject(Request $request, Sale $sale)
    {
        $project = Project::create([
            'customer_id' => $sale->customer_id,
            'name' => $sale->name,
            'description' => $sale->notes,
            'status' => 'planned',
        ]);

        $sale->update(['stage' => 'won']);

        return redirect(route('projects.edit', $project->id))->with('success', __('record_saved_message'));
    }
}
