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

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'paid_on' => 'required|date',
            'method' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
        ]);

        $invoice->payments()->create($data);
        $invoice->recalculate();

        return redirect(route('invoices.show', $invoice->id))->with('success', __('record_saved_message'));
    }

    // The route uses scopeBindings(), so a payment of another invoice is a 404 here.
    public function destroy(Request $request, Invoice $invoice, Payment $payment)
    {
        $payment->delete();
        $invoice->recalculate();

        return redirect(route('invoices.show', $invoice->id))->with('success', __('record_deleted_message'));
    }
}
