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
use App\Models\Tag;
use Illuminate\Http\Request;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        $q = $request->query('q');

        if ($q) {
            $query->where(function ($query) use ($q) {
                $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('company', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhere('website', 'like', "%{$q}%");
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

        $customers = $query->cursorPaginate(25);

        return view('pages.customers', [
            'customers' => $customers,
            'q' => $q,
            'status' => $status,
            'type' => $type,
            'tags' => Tag::toOptions(),
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.customers-edit', [
            'customer' => new Customer(),
            'tags' => Tag::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $customer = Customer::create($request->all());

        if ($request->has('tags')) {
            $customer->tags()->sync($request->input('tags', []));
        }

        return redirect(route('customers.show', ['customer' => $customer->id]))->with('success', __('record_saved_message'));
    }

    public function show(Request $request, Customer $customer)
    {
        return view('pages.customers-show', [
            'customer' => $customer->load(['contacts', 'projects', 'contracts', 'sales', 'tags']),
        ]);
    }

    public function edit(Request $request, Customer $customer)
    {
        return view('pages.customers-edit', [
            'customer' => $customer->load('tags'),
            'tags' => Tag::toOptions(),
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|min:2',
            'email' => 'nullable|email',
        ]);

        $customer->fill($request->input());
        $customer->save();

        if ($request->has('tags')) {
            $customer->tags()->sync($request->input('tags', []));
        }

        return redirect(route('customers.show', $customer->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Request $request, Customer $customer)
    {
        $customer->delete();

        return redirect(route('customers'))->with('success', __('record_deleted_message'));
    }
}
