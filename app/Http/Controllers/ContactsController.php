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
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        sort_query($query, $sort, $direction);

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
        $request->validate(
            ['first_name' => 'required'] + $this->portalRules($request) + CustomField::rules('contacts'),
            [],
            CustomField::attributes('contacts'),
        );

        $data = $request->all();
        $data['customer_id'] = $customer->id;

        $contact = new Contact($data);
        $this->setPassword($request, $contact);
        $contact->save();

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
        $request->validate(
            ['first_name' => 'required|min:2'] + $this->portalRules($request, $contact) + CustomField::rules('contacts'),
            [],
            CustomField::attributes('contacts'),
        );

        $contact->fill($request->all());
        $this->setPassword($request, $contact);
        $contact->save();

        return redirect(route('customers.contacts.edit', [$customer->id, $contact->id]))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Customer $customer, Contact $contact)
    {
        $contact->delete();

        return redirect(route('customers.contacts', $customer->id))->with('success', __('record_deleted_message'));
    }

    /**
     * A portal login needs an email no other portal contact uses, since that is
     * what the contact signs in with.
     */
    private function portalRules(Request $request, ?Contact $contact = null): array
    {
        if (!$request->boolean('has_portal_access')) {
            return ['email' => 'nullable|email'];
        }

        return [
            'email' => ['required', 'email', Rule::unique('contacts')->where('has_portal_access', true)->ignore($contact)],
            'password' => [$contact?->password ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    // A blank password field keeps the current one.
    private function setPassword(Request $request, Contact $contact): void
    {
        if (filled($request->input('password'))) {
            $contact->password = $request->input('password');
        }
    }
}
