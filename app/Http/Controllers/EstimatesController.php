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
use App\Models\Estimate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EstimatesController extends Controller
{
    public function index(Request $request)
    {
        $query = Estimate::with('customer');

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%");
                    });
            });
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        sort_query($query, $request->query('sort', 'created_at'), $request->query('direction', 'desc'));

        return view('pages.estimates', [
            'estimates' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $estimate = new Estimate([
            'customer_id' => $request->query('customer_id'),
            'issue_date' => today(),
            'expiry_date' => today()->addDays(30),
        ]);

        return view('pages.estimates-edit', [
            'estimate' => $estimate,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateEstimate($request);

        // The header and its lines are saved together or not at all.
        $estimate = DB::transaction(function () use ($data) {
            $estimate = Estimate::create($data);
            $estimate->syncItems($data['items'] ?? []);

            return $estimate;
        });

        return redirect(route('estimates.edit', $estimate->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Estimate $estimate)
    {
        return view('pages.estimates-show', [
            'estimate' => $estimate->load('customer', 'items', 'invoice'),
        ]);
    }

    public function print(Request $request, Estimate $estimate)
    {
        return view('pages.document-print', [
            'document' => $estimate->load('customer', 'items'),
            'title' => __('estimate'),
            'backUrl' => route('estimates.show', $estimate->id),
            'dates' => [__('issue_date') => $estimate->issue_date, __('expiry_date') => $estimate->expiry_date],
        ]);
    }

    public function edit(Request $request, Estimate $estimate)
    {
        return view('pages.estimates-edit', [
            'estimate' => $estimate->load('items'),
            'customers' => Customer::toOptions(),
        ]);
    }

    public function update(Request $request, Estimate $estimate)
    {
        $data = $this->validateEstimate($request, $estimate);

        DB::transaction(function () use ($estimate, $data) {
            $estimate->update($data);
            $estimate->syncItems($data['items'] ?? []);
        });

        return redirect(route('estimates.edit', $estimate->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Estimate $estimate)
    {
        $estimate->delete();

        return redirect(route('estimates'))->with('success', __('record_deleted_message'));
    }

    public function convertToInvoice(Request $request, Estimate $estimate)
    {
        $invoice = $estimate->convertToInvoice();

        return redirect(route('invoices.edit', $invoice->id))->with('success', __('record_saved_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:estimates,id',
        ]);

        Estimate::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('estimates'))->with('success', __('records_deleted_message'));
    }

    private function validateEstimate(Request $request, ?Estimate $estimate = null): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'number' => ['nullable', 'string', 'max:50', Rule::unique('estimates')->ignore($estimate)],
            'status' => 'required|in:draft,sent,accepted,declined',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
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
