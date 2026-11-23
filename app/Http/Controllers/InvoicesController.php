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

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

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
        $query->whereDisplayStatus($status);

        sort_query($query, $request->query('sort', 'number'), $request->query('direction', 'desc'));

        return view('pages.invoices', [
            'invoices' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $invoice = new Invoice([
            'customer_id' => $request->query('customer_id'),
            'issue_date' => today(),
            'due_date' => today()->addDays(30),
        ]);

        return view('pages.invoices-edit', [
            'invoice' => $invoice,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);

        // The header and its lines are saved together or not at all.
        $invoice = DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            $invoice->syncItems($data['items'] ?? []);

            return $invoice;
        });

        return redirect(route('invoices.edit', $invoice->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Invoice $invoice)
    {
        return view('pages.invoices-show', [
            'invoice' => $invoice->load('customer', 'items', 'payments', 'creditNotes'),
        ]);
    }

    public function print(Request $request, Invoice $invoice)
    {
        return view('pages.document-print', [
            'document' => $invoice->load('customer', 'items'),
            'title' => __('invoice'),
            'backUrl' => route('invoices.show', $invoice->id),
            'dates' => [__('issue_date') => $invoice->issue_date, __('due_date') => $invoice->due_date],
        ]);
    }

    public function edit(Request $request, Invoice $invoice)
    {
        return view('pages.invoices-edit', [
            'invoice' => $invoice->load('items'),
            'customers' => Customer::toOptions(),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validateInvoice($request, $invoice);

        // A number cleared on edit keeps the one it had; only new documents get one assigned.
        if (blank($data['number'] ?? null)) {
            unset($data['number']);
        }

        DB::transaction(function () use ($invoice, $data) {
            $invoice->update($data);
            $invoice->syncItems($data['items'] ?? []);

            // Credits from another customer or currency stop counting once the invoice moves away from them.
            $mismatched = CreditNote::where('invoice_id', $invoice->id)
                ->where(fn ($query) => $query->where('customer_id', '!=', $invoice->customer_id)
                    ->orWhere('currency', '!=', $invoice->currency))
                ->get();

            if ($mismatched->isNotEmpty()) {
                $mismatched->each(fn (CreditNote $creditNote) => $creditNote->invoice()->dissociate()->save());
                $invoice->recalculate();
            }
        });

        return redirect(route('invoices.edit', $invoice->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $invoice->delete();

        return redirect(route('invoices'))->with('success', __('record_deleted_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:invoices,id',
        ]);

        // One by one, so model events release applied credit notes.
        Invoice::whereIn('id', $request->input('ids'))->get()->each->delete();

        return redirect(route('invoices'))->with('success', __('records_deleted_message'));
    }

    private function validateInvoice(Request $request, ?Invoice $invoice = null): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'number' => ['nullable', 'string', 'max:50', Rule::unique('invoices')->ignore($invoice)],
            'status' => 'required|in:draft,sent,cancelled',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'recurring_interval' => ['nullable', Rule::in(array_keys(Invoice::recurringIntervals()))],
            'next_recurring_date' => 'nullable|required_with:recurring_interval|date',
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
