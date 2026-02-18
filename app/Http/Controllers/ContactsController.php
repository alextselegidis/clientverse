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

use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    public function index(Request $request, Customer $customer)
    {
        $query = $customer->contacts();

        $q = $request->query('q');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $sort = $request->query('sort', 'first_name');
        $direction = $request->query('direction', 'asc');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $contacts = $query->paginate(25);

        return view('pages.contacts', [
            'customer' => $customer,
            'contacts' => $contacts,
            'q' => $q,
        ]);
    }

    public function create(Request $request, Customer $customer)
    {
        return view('pages.contacts-edit', [
            'customer' => $customer,
            'contact' => new Contact(),
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'first_name' => 'required',
        ]);

        $data = $request->all();
        $data['customer_id'] = $customer->id;

        $contact = Contact::create($data);

        return redirect(route('customers.contacts.show', [$customer->id, $contact->id]))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Customer $customer, Contact $contact)
    {
        return view('pages.contacts-show', [
            'customer' => $customer,
            'contact' => $contact,
        ]);
    }

    public function edit(Request $request, Customer $customer, Contact $contact)
    {
        return view('pages.contacts-edit', [
            'customer' => $customer,
            'contact' => $contact,
        ]);
    }

    public function update(Request $request, Customer $customer, Contact $contact)
    {
        $request->validate([
            'first_name' => 'required|min:2',
            'email' => 'nullable|email',
        ]);

        $contact->fill($request->all());
        $contact->save();

        return redirect(route('customers.contacts.edit', [$customer->id, $contact->id]))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Customer $customer, Contact $contact)
    {
        $contact->delete();

        return redirect(route('customers.contacts', $customer->id))->with('success', __('record_deleted_message'));
    }
}
