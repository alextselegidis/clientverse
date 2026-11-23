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
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Throwable;

/**
 * Online invoice payments through PayPal Checkout (Orders API v2), spoken to
 * over plain HTTP. The customer approves the order on PayPal's page and comes
 * back to return(), where the server captures it; only a completed capture,
 * as PayPal reports it, is recorded. No webhook is needed for that reason.
 */
class PayPalController extends Controller
{
    /**
     * Currencies PayPal takes in whole units only.
     */
    private const ZERO_DECIMAL = ['HUF', 'JPY', 'TWD'];

    public function checkout(Invoice $invoice)
    {
        abort_unless($invoice->canBePaidOnline('paypal'), 404);

        try {
            $response = $this->createOrder($invoice);
        } catch (Throwable $exception) {
            report($exception);
            $response = null;
        }

        $approve = collect($response?->json('links') ?? [])->firstWhere('rel', 'payer-action')['href'] ?? null;

        if (!$response || $response->failed() || !$approve) {
            $response && report(new RuntimeException('PayPal order failed: ' . $response->body()));

            return redirect($invoice->publicUrl())->with('error', __('online_payment_failed_message'));
        }

        return redirect()->away($approve);
    }

    private function createOrder(Invoice $invoice): Response
    {
        return $this->api()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'custom_id' => (string) $invoice->id,
                'description' => __('invoice') . ' ' . $invoice->number,
                'amount' => [
                    'currency_code' => strtoupper($invoice->currency),
                    'value' => $this->money($invoice->balance, $invoice->currency),
                ],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => route('public.paypal.return'),
                'cancel_url' => $invoice->publicUrl(),
            ]]],
        ]);
    }

    /**
     * PayPal sends the customer here with ?token=<order id> after approval.
     */
    public function return(Request $request)
    {
        $orderId = (string) $request->query('token');

        abort_unless(preg_match('/^[A-Z0-9]{5,40}$/', $orderId), 404);

        $order = $this->api()->post("/v2/checkout/orders/{$orderId}/capture", (object) []);

        // A reload captures twice; PayPal refuses the second, so read the order as it stands.
        if ($order->status() === 422) {
            $order = $this->api()->get("/v2/checkout/orders/{$orderId}");
        }

        $unit = $order->json('purchase_units.0');
        $capture = $unit['payments']['captures'][0] ?? null;
        $invoiceId = $unit['custom_id'] ?? null;

        if ($order->failed() || !$invoiceId || ($capture['status'] ?? null) !== 'COMPLETED') {
            report(new RuntimeException('PayPal capture failed: ' . $order->body()));
            $invoice = $invoiceId ? Invoice::find($invoiceId) : null;
            abort_unless($invoice, 404);

            return redirect($invoice->publicUrl())->with('error', __('online_payment_failed_message'));
        }

        Invoice::recordOnlinePayment($invoiceId, (float) $capture['amount']['value'], 'PayPal', $capture['id']);

        return redirect(URL::signedRoute('public.invoices.show', ['invoice' => $invoiceId, 'paid' => 1]));
    }

    /**
     * An authorised client for the live or sandbox API, per Setup > Billing.
     */
    private function api(): PendingRequest
    {
        $base = setting('paypal_sandbox') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

        $token = Http::asForm()->withBasicAuth(setting('paypal_client_id'), setting('paypal_secret'))
            ->post($base . '/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json('access_token');

        return Http::baseUrl($base)->withToken($token)->acceptJson()->asJson();
    }

    private function money(float $amount, string $currency): string
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL, true)
            ? (string) (int) round($amount)
            : number_format($amount, 2, '.', '');
    }
}
