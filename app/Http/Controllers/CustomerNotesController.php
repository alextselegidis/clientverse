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
use App\Models\CustomerNote;
use Illuminate\Http\Request;

class CustomerNotesController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'content' => 'required|min:1',
        ]);

        CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'content' => $request->input('content'),
        ]);

        return redirect(route('customers.show', $customer->id))->with('success', __('record_saved_message'));
    }

    public function update(Request $request, Customer $customer, CustomerNote $note)
    {
        $this->assertNoteBelongsToCustomer($note, $customer);

        $request->validate([
            'content' => 'required|min:1',
        ]);

        $note->update([
            'content' => $request->input('content'),
        ]);

        return redirect(route('customers.show', $customer->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Customer $customer, CustomerNote $note)
    {
        $this->assertNoteBelongsToCustomer($note, $customer);

        $note->delete();

        return redirect(route('customers.show', $customer->id))->with('success', __('record_deleted_message'));
    }

    /**
     * Both route parameters are resolved on their own, so a note of one customer
     * reaches the route of another unless they are checked against each other.
     */
    private function assertNoteBelongsToCustomer(CustomerNote $note, Customer $customer): void
    {
        if ($note->customer_id !== $customer->id) {
            abort(404);
        }
    }
}
