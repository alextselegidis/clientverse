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

use App\Models\Customer;
use App\Models\Proposal;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProposalsController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with('customer');

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('number', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        sort_query($query, $request->query('sort', 'number'), $request->query('direction', 'desc'));

        return view('pages.proposals', [
            'proposals' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        // Opened from a sale, the proposal starts with that sale's customer and name.
        $sale = Sale::find($request->query('sale_id'));

        $proposal = new Proposal([
            'customer_id' => $request->query('customer_id', $sale?->customer_id),
            'sale_id' => $sale?->id,
            'subject' => $sale?->name,
            'issue_date' => today(),
            'open_till' => today()->addDays(30),
        ]);

        return view('pages.proposals-edit', [
            'proposal' => $proposal,
            'customers' => Customer::toOptions(),
            'sales' => Sale::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProposal($request);

        // The header and its lines are saved together or not at all.
        $proposal = DB::transaction(function () use ($data) {
            $proposal = Proposal::create($data);
            $proposal->syncItems($data['items'] ?? []);

            return $proposal;
        });

        return redirect(route('proposals.edit', $proposal->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Proposal $proposal)
    {
        return view('pages.proposals-show', [
            'proposal' => $proposal->load('customer', 'items', 'sale', 'estimate'),
        ]);
    }

    public function print(Request $request, Proposal $proposal)
    {
        return view('pages.document-print', [
            'document' => $proposal->load('customer', 'items'),
            'title' => __('proposal'),
            'backUrl' => route('proposals.show', $proposal->id),
            'dates' => [__('issue_date') => $proposal->issue_date, __('open_till') => $proposal->open_till],
        ]);
    }

    public function edit(Request $request, Proposal $proposal)
    {
        return view('pages.proposals-edit', [
            'proposal' => $proposal->load('items'),
            'customers' => Customer::toOptions(),
            'sales' => Sale::toOptions(),
        ]);
    }

    public function update(Request $request, Proposal $proposal)
    {
        $data = $this->validateProposal($request, $proposal);

        // A number cleared on edit keeps the one it had; only new documents get one assigned.
        if (blank($data['number'] ?? null)) {
            unset($data['number']);
        }

        DB::transaction(function () use ($proposal, $data) {
            $proposal->update($data);
            $proposal->syncItems($data['items'] ?? []);
        });

        return redirect(route('proposals.edit', $proposal->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Proposal $proposal)
    {
        $proposal->delete();

        return redirect(route('proposals'))->with('success', __('record_deleted_message'));
    }

    public function convertToEstimate(Request $request, Proposal $proposal)
    {
        abort_unless($request->user()->hasPermission('estimates', 'edit'), 403);

        $estimate = $proposal->convertToEstimate();

        return redirect(route('estimates.edit', $estimate->id))->with('success', __('record_saved_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:proposals,id',
        ]);

        Proposal::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('proposals'))->with('success', __('records_deleted_message'));
    }

    private function validateProposal(Request $request, ?Proposal $proposal = null): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_id' => 'nullable|exists:sales,id',
            'subject' => 'required|string|max:255',
            'content' => 'nullable|string',
            'number' => ['nullable', 'string', 'max:50', Rule::unique('proposals')->ignore($proposal)],
            'status' => 'required|in:draft,sent,accepted,declined',
            'issue_date' => 'nullable|date',
            'open_till' => 'nullable|date|after_or_equal:issue_date',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0|max:99999999',
            'items.*.unit_price' => 'nullable|numeric|between:-9999999999.99,9999999999.99',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
    }
}
