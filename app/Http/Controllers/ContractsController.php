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

class ContractsController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with('customer');

        $q = $request->query('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('title', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $type = $request->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $contracts = $query->cursorPaginate(25);

        return view('pages.contracts', [
            'contracts' => $contracts,
            'q' => $q,
            'status' => $status,
            'type' => $type,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.contracts-edit', [
            'contract' => new Contract(),
            'customers' => Customer::toOptions(),
            'projects' => Project::toOptions(),
            'sales' => Sale::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $contract = Contract::create($request->all());

        return redirect(route('contracts.show', $contract->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Contract $contract)
    {
        return view('pages.contracts-show', [
            'contract' => $contract->load(['customer', 'project', 'sale']),
        ]);
    }

    public function edit(Request $request, Contract $contract)
    {
        return view('pages.contracts-edit', [
            'contract' => $contract,
            'customers' => Customer::toOptions(),
            'projects' => Project::toOptions(),
            'sales' => Sale::toOptions(),
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'title' => 'required|min:2',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $contract->fill($request->all());
        $contract->save();

        return redirect(route('contracts.show', $contract->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Contract $contract)
    {
        $contract->delete();

        return redirect(route('contracts'))->with('success', __('record_deleted_message'));
    }
}
