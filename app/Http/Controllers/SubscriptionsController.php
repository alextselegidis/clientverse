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
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use RuntimeException;

class SubscriptionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with('customer');

        $q = $request->query('q');
        $status = $request->query('status');

        if ($q) {
            $query->where(fn ($query) => $query->where('name', 'like', "%{$q}%")
                ->orWhereHas('customer', fn ($query) => $query->where('name', 'like', "%{$q}%")));
        }

        if ($status) {
            $query->where('status', $status);
        }

        sort_query($query, $request->query('sort', 'created_at'), $request->query('direction', 'desc'));

        return view('pages.subscriptions', [
            'subscriptions' => $query->paginate(25)->withQueryString(),
            'q' => $q,
            'status' => $status,
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.subscriptions-edit', [
            'subscription' => new Subscription([
                'customer_id' => $request->query('customer_id'),
                'currency' => default_currency(),
                'interval' => 'month',
                'tax_rate' => 0,
            ]),
            'customers' => Customer::toOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $subscription = Subscription::create($this->validateSubscription($request));

        return redirect(route('subscriptions.show', $subscription->id))->with('success', __('record_saved_message'));
    }

    public function show(Subscription $subscription)
    {
        return view('pages.subscriptions-show', ['subscription' => $subscription->load('customer', 'invoices')]);
    }

    public function edit(Subscription $subscription)
    {
        abort_unless($subscription->isEditable(), 403);

        return view('pages.subscriptions-edit', ['subscription' => $subscription, 'customers' => Customer::toOptions()]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        abort_unless($subscription->isEditable(), 403);

        $subscription->update($this->validateSubscription($request));

        return redirect(route('subscriptions.show', $subscription->id))->with('success', __('record_saved_message'));
    }

    /**
     * Stop billing. A subscription Stripe knows about is cancelled there first,
     * so the customer is never charged for a subscription we show as cancelled.
     */
    public function cancel(Subscription $subscription)
    {
        if ($subscription->status === 'canceled') {
            return back();
        }

        if ($subscription->stripe_subscription_id) {
            $response = Http::withToken(setting('stripe_secret_key'))
                ->delete('https://api.stripe.com/v1/subscriptions/' . $subscription->stripe_subscription_id);

            // Already gone at Stripe is as good as cancelled.
            if ($response->failed() && $response->json('error.code') !== 'resource_missing') {
                report(new RuntimeException('Stripe cancel failed: ' . $response->body()));

                return back()->with('error', __('subscription_cancel_failed_message'));
            }
        }

        $subscription->syncStripeStatus('canceled');

        return back()->with('success', __('subscription_canceled_message'));
    }

    public function destroy(Subscription $subscription)
    {
        // Only one that never started can go; the rest keep their billing history.
        abort_unless($subscription->isEditable() || $subscription->status === 'canceled', 403);

        $subscription->delete();

        return redirect(route('subscriptions'))->with('success', __('record_deleted_message'));
    }

    private function validateSubscription(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'currency' => 'required|string|size:3',
            'amount' => 'required|numeric|gt:0|max:999999.99',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'interval' => ['required', Rule::in(array_keys(Subscription::intervals()))],
        ]);
    }
}
