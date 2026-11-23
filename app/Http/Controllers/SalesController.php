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
use App\Models\CustomField;
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

        $sort = $request->query('sort', 'value');
        $direction = $request->query('direction', 'desc');

        sort_query($query, $sort, $direction);

        $sales = $query->paginate(25)->withQueryString();

        return view('pages.sales', [
            'sales' => $sales,
            'q' => $q,
            'stage' => $stage,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function board(Request $request)
    {
        // Open deals all show; won and lost ones only from the last 90 days, or the
        // closed columns grow without end.
        $sales = Sale::with('customer')
            ->where(fn ($query) => $query
                ->whereNotIn('stage', ['won', 'lost'])
                ->orWhere('updated_at', '>=', now()->subDays(90)))
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('stage');

        return view('pages.sales-board', [
            'columns' => collect(Sale::stages())->map(fn ($label, $stage) => [
                'label' => $label,
                'sales' => $sales->get($stage, collect()),
            ]),
        ]);
    }

    public function updateStage(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(Sale::stages())),
        ]);

        $sale->update($data);

        return redirect(route('sales.board'))->with('success', __('record_saved_message'));
    }

    public function create(Request $request)
    {
        $sale = new Sale();
        
        if ($request->has('customer_id')) {
            $sale->customer_id = $request->query('customer_id');
        }
        
        return view('pages.sales-edit', [
            'sale' => $sale,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'customer_id' => 'required|exists:customers,id',
        ] + CustomField::rules('sales'), [], CustomField::attributes('sales'));

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
        ] + CustomField::rules('sales'), [], CustomField::attributes('sales'));

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
        abort_unless($request->user()->hasPermission('contracts', 'edit'), 403);

        $contract = Contract::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'title' => $sale->name,
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
        abort_unless($request->user()->hasPermission('projects', 'edit'), 403);

        $project = Project::create([
            'customer_id' => $sale->customer_id,
            'name' => $sale->name,
            'description' => $sale->notes,
            'status' => 'planned',
        ]);

        $sale->update(['stage' => 'won']);

        return redirect(route('projects.edit', $project->id))->with('success', __('record_saved_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:sales,id',
        ]);

        Sale::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('sales'))->with('success', __('records_deleted_message'));
    }
}
