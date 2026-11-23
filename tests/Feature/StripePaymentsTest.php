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

namespace Tests\Feature;

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StripePaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(): Invoice
    {
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 120.5]]);

        return $invoice;
    }

    private function webhook(array $event, string $secret = 'whsec_test', ?int $time = null)
    {
        $payload = json_encode($event);
        $time ??= time();
        $signature = hash_hmac('sha256', $time . '.' . $payload, $secret);

        return $this->call('POST', route('api.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}",
        ], $payload);
    }

    public function test_the_pay_button_only_shows_with_a_stripe_key_and_starts_checkout_for_the_balance(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['url' => 'https://checkout.stripe.com/c/abc'])]);
        $invoice = $this->invoice();

        $this->get($invoice->publicUrl())->assertDontSee(__('pay_amount', ['amount' => 'EUR 120.50']));

        setting(['stripe_secret_key' => 'sk_test_123']);
        $this->get($invoice->publicUrl())->assertSee(__('pay_amount', ['amount' => 'EUR 120.50']));

        $this->post(URL::signedRoute('public.invoices.pay', ['invoice' => $invoice->id]))
            ->assertRedirect('https://checkout.stripe.com/c/abc');
        $this->post(route('public.invoices.pay', $invoice))->assertForbidden();

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer sk_test_123')
            && $request['line_items'][0]['price_data']['unit_amount'] == 12050
            && $request['metadata']['invoice_id'] == $invoice->id);
    }

    public function test_a_signed_webhook_records_the_payment_once(): void
    {
        setting(['stripe_webhook_secret' => 'whsec_test']);
        $invoice = $this->invoice();
        $event = ['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_1', 'payment_intent' => 'pi_1', 'payment_status' => 'paid', 'amount_total' => 12050,
            'currency' => 'eur', 'metadata' => ['invoice_id' => (string) $invoice->id],
        ]]];

        $this->webhook($event, 'whsec_wrong')->assertStatus(400);
        $this->webhook($event, time: time() - 3600)->assertStatus(400);
        $this->webhook($event)->assertNoContent();
        $this->webhook($event)->assertNoContent();

        $invoice->refresh();
        $this->assertSame(1, $invoice->payments()->count());
        $this->assertSame('paid', $invoice->display_status);
        $this->assertSame('pi_1', $invoice->payments()->first()->reference);
    }
}
