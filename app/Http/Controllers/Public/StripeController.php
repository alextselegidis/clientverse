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

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;

/**
 * Online invoice payments through Stripe Checkout, spoken to over plain HTTP so
 * there is no SDK to install. The customer pays on Stripe's page; the payment is
 * only recorded when Stripe's signed webhook says it went through.
 */
class StripeController extends Controller
{
    private const API = 'https://api.stripe.com/v1/checkout/sessions';

    /**
     * Currencies Stripe counts in whole units rather than hundredths.
     */
    private const ZERO_DECIMAL = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

    public function checkout(Invoice $invoice)
    {
        abort_unless($invoice->canBePaidOnline('stripe'), 404);

        $response = Http::asForm()->withToken(setting('stripe_secret_key'))->post(self::API, [
            'mode' => 'payment',
            'success_url' => URL::signedRoute('public.invoices.show', ['invoice' => $invoice->id, 'paid' => 1]),
            'cancel_url' => $invoice->publicUrl(),
            'client_reference_id' => $invoice->id,
            'metadata' => ['invoice_id' => $invoice->id],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($invoice->currency),
                    'unit_amount' => (int) round($invoice->balance * $this->unit($invoice->currency)),
                    'product_data' => ['name' => __('invoice') . ' ' . $invoice->number],
                ],
            ]],
        ]);

        if ($response->failed() || !$response->json('url')) {
            report(new RuntimeException('Stripe checkout failed: ' . $response->body()));

            return redirect($invoice->publicUrl())->with('error', __('online_payment_failed_message'));
        }

        return redirect()->away($response->json('url'));
    }

    public function showSubscription(Subscription $subscription)
    {
        return view('pages.public-subscription', ['subscription' => $subscription]);
    }

    /**
     * Start Stripe Checkout in subscription mode. The price is sent inline, so
     * nothing has to be set up in the Stripe dashboard first.
     */
    public function subscribe(Subscription $subscription)
    {
        abort_unless($subscription->canBeSubscribedTo(), 404);

        $metadata = ['subscription_id' => $subscription->id];
        $subscribed = URL::signedRoute('public.subscriptions.show', [
            'subscription' => $subscription->id,
            'subscribed' => 1,
        ]);
        $response = Http::asForm()->withToken(setting('stripe_secret_key'))->post(self::API, array_filter([
            'mode' => 'subscription',
            'success_url' => $subscribed,
            'cancel_url' => $subscription->publicUrl(),
            'client_reference_id' => $subscription->id,
            'customer_email' => $subscription->customer?->email,
            'metadata' => $metadata,
            'subscription_data' => ['metadata' => $metadata],
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($subscription->currency),
                    'unit_amount' => (int) round($subscription->gross_amount * $this->unit($subscription->currency)),
                    'recurring' => ['interval' => $subscription->interval],
                    'product_data' => ['name' => $subscription->name],
                ],
            ]],
        ]));

        if ($response->failed() || !$response->json('url')) {
            report(new RuntimeException('Stripe subscription checkout failed: ' . $response->body()));

            return redirect($subscription->publicUrl())->with('error', __('online_payment_failed_message'));
        }

        // The link has been used; staff see the subscription as waiting on the customer.
        if ($subscription->status === 'draft') {
            $subscription->forceFill(['status' => 'pending'])->save();
        }

        return redirect()->away($response->json('url'));
    }

    public function webhook(Request $request)
    {
        $secret = setting('stripe_webhook_secret');

        abort_if(blank($secret), 404);
        abort_unless($this->hasValidSignature($request, $secret), 400);

        $event = $request->json()->all();
        $session = $event['data']['object'] ?? [];

        // Card payments are paid on completion; bank debits arrive later as async_payment_succeeded.
        $types = ['checkout.session.completed', 'checkout.session.async_payment_succeeded'];

        if (in_array($event['type'] ?? null, $types, true) && ($session['mode'] ?? 'payment') === 'subscription') {
            $this->linkSubscription($session);
        } elseif (in_array($event['type'] ?? null, $types, true) && ($session['payment_status'] ?? null) === 'paid') {
            $this->recordPayment($session);
        }

        $this->handleSubscriptionEvent($event['type'] ?? '', $session);

        return response()->noContent();
    }

    /**
     * Stripe signs "timestamp.body" with the endpoint secret and sends
     * `Stripe-Signature: t=...,v1=...`. Old timestamps are refused so a captured
     * request cannot be replayed.
     */
    private function hasValidSignature(Request $request, string $secret): bool
    {
        $timestamp = 0;
        $signatures = [];

        foreach (explode(',', (string) $request->header('Stripe-Signature')) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        return abs(time() - $timestamp) <= 300
            && collect($signatures)->contains(fn ($signature) => hash_equals($expected, $signature));
    }

    private function recordPayment(array $session): void
    {
        $reference = $session['payment_intent'] ?? $session['id'] ?? null;
        $invoiceId = $session['metadata']['invoice_id'] ?? null;

        if ($reference && $invoiceId) {
            $amount = $session['amount_total'] / $this->unit($session['currency'] ?? '');
            Invoice::recordOnlinePayment($invoiceId, $amount, 'Stripe', $reference);
        }
    }

    /**
     * Checkout finished: remember which Stripe subscription and customer belong to ours.
     */
    private function linkSubscription(array $session): void
    {
        $subscription = Subscription::find($session['metadata']['subscription_id'] ?? null);

        if ($subscription && !empty($session['subscription'])) {
            $subscription->forceFill([
                'stripe_subscription_id' => $session['subscription'],
                'stripe_customer_id' => $session['customer'] ?? null,
                'status' => $subscription->status === 'canceled' ? 'canceled' : 'active',
            ])->save();
        }
    }

    /**
     * Stripe's subscription billing events. The first invoice can arrive before
     * checkout.session.completed, so it is matched by our id in its metadata too.
     */
    private function handleSubscriptionEvent(string $type, array $object): void
    {
        if (str_starts_with($type, 'invoice.')) {
            $details = $object['parent']['subscription_details'] ?? $object['subscription_details'] ?? [];
            $stripeId = $details['subscription'] ?? $object['subscription'] ?? null;
            $subscription = Subscription::find($details['metadata']['subscription_id'] ?? null)
                ?? ($stripeId ? Subscription::firstWhere('stripe_subscription_id', $stripeId) : null);

            if (!$subscription) {
                return;
            }

            if ($stripeId && !$subscription->stripe_subscription_id) {
                $subscription->forceFill([
                    'stripe_subscription_id' => $stripeId,
                    'stripe_customer_id' => $object['customer'] ?? null,
                ])->save();
            }

            if ($type === 'invoice.paid' && ($object['amount_paid'] ?? 0) > 0) {
                $amount = $object['amount_paid'] / $this->unit($object['currency'] ?? '');
                $subscription->recordStripeInvoice($object, $amount);
            } elseif ($type === 'invoice.payment_failed') {
                $subscription->syncStripeStatus('past_due');
            }
        } elseif (in_array($type, ['customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            Subscription::firstWhere('stripe_subscription_id', $object['id'] ?? null)
                ?->syncStripeStatus($type === 'customer.subscription.deleted' ? 'canceled' : ($object['status'] ?? ''));
        }
    }

    private function unit(string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL, true) ? 1 : 100;
    }
}
