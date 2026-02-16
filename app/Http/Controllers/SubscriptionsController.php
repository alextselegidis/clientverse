<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::query();

        $q = $request->query('q');
        if ($q) {
            $query->where('plan', 'like', "%$q%");
        }

        $sort = $request->query('sort');
        $direction = $request->query('direction');
        if ($sort && $direction) {
            $query->orderBy($sort, $direction);
        }

        $subscriptions = $query->cursorPaginate(25);

        return view('pages.subscriptions', compact('subscriptions', 'q'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required',
        ]);

        $subscription = Subscription::create($request->all());

        return redirect(route('subscriptions.edit', $subscription->id));
    }

    public function show(Subscription $subscription)
    {
        return view('pages.subscriptions-show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $customerOptions = Customer::toOptions();

        return view('pages.subscriptions-edit', compact('subscription', 'customerOptions'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'plan' => 'required|min:2',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $subscription->fill($request->all());
        $subscription->save();

        return redirect(route('subscriptions.edit', $subscription->id))->with('success', __('record_saved_message'));
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->back()->with('success', __('record_deleted_message'));
    }
}
