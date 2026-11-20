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
use Illuminate\Validation\Rule;

class CreditNotesController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditNote::with('customer');

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

        return view('pages.credit-notes', [
            'creditNotes' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        $creditNote = new CreditNote([
            'customer_id' => $request->query('customer_id'),
            'issue_date' => today(),
        ]);

        return view('pages.credit-notes-edit', [
            'creditNote' => $creditNote,
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCreditNote($request);

        $creditNote = CreditNote::create($data);
        $creditNote->syncItems($data['items'] ?? []);

        return redirect(route('credit-notes.edit', $creditNote->id))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, CreditNote $creditNote)
    {
        return view('pages.credit-notes-show', [
            'creditNote' => $creditNote->load('customer', 'items', 'invoice'),
            'openInvoices' => Invoice::where('customer_id', $creditNote->customer_id)
                ->where('currency', $creditNote->currency)
                ->where('status', 'sent')
                ->whereColumn('amount_paid', '<', 'total')
                ->get(),
        ]);
    }

    public function print(Request $request, CreditNote $creditNote)
    {
        return view('pages.document-print', [
            'document' => $creditNote->load('customer', 'items'),
            'title' => __('credit_note'),
            'backUrl' => route('credit-notes.show', $creditNote->id),
            'dates' => [__('issue_date') => $creditNote->issue_date],
        ]);
    }

    public function edit(Request $request, CreditNote $creditNote)
    {
        return view('pages.credit-notes-edit', [
            'creditNote' => $creditNote->load('items'),
            'customers' => Customer::toOptions(),
        ]);
    }

    public function update(Request $request, CreditNote $creditNote)
    {
        $data = $this->validateCreditNote($request, $creditNote);

        $creditNote->update($data);
        $creditNote->syncItems($data['items'] ?? []);

        return redirect(route('credit-notes.edit', $creditNote->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, CreditNote $creditNote)
    {
        $creditNote->delete();

        return redirect(route('credit-notes'))->with('success', __('record_deleted_message'));
    }

    public function apply(Request $request, CreditNote $creditNote)
    {
        $request->validate(['invoice_id' => 'required|exists:invoices,id']);

        $invoice = Invoice::findOrFail($request->input('invoice_id'));

        if ($invoice->customer_id !== $creditNote->customer_id || $invoice->currency !== $creditNote->currency) {
            return back()->with('error', __('credit_note_must_match_message'));
        }

        $creditNote->invoice()->associate($invoice)->save();

        return redirect(route('credit-notes.show', $creditNote->id))->with('success', __('record_saved_message'));
    }

    public function unapply(CreditNote $creditNote)
    {
        $creditNote->invoice()->dissociate()->save();

        return redirect(route('credit-notes.show', $creditNote->id))->with('success', __('record_saved_message'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:credit_notes,id',
        ]);

        CreditNote::whereIn('id', $request->input('ids'))->delete();

        return redirect(route('credit-notes'))->with('success', __('records_deleted_message'));
    }

    private function validateCreditNote(Request $request, ?CreditNote $creditNote = null): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'number' => ['nullable', 'string', 'max:50', Rule::unique('credit_notes')->ignore($creditNote)],
            'status' => 'required|in:draft,issued',
            'issue_date' => 'nullable|date',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'items' => 'array',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);
    }
}
