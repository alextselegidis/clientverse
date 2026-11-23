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

class PayPalPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(): Invoice
    {
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 120.5]]);
        setting(['paypal_client_id' => 'client', 'paypal_secret' => 'secret', 'paypal_sandbox' => '1']);

        return $invoice;
    }

    private function capturedOrder(Invoice $invoice, string $status = 'COMPLETED'): array
    {
        return ['status' => 'COMPLETED', 'purchase_units' => [[
            'custom_id' => (string) $invoice->id,
            'payments' => ['captures' => [['id' => 'CAP123', 'status' => $status, 'amount' => ['value' => '120.50']]]],
        ]]];
    }

    public function test_checkout_creates_an_order_for_the_balance_and_sends_the_customer_to_paypal(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response(['links' => [
                ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/x'],
                ['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER1'],
            ]], 201),
        ]);
        $invoice = $this->invoice();

        $this->get($invoice->publicUrl())->assertSee(__('pay_with_paypal'))->assertDontSee(__('pay_amount', ['amount' => 'EUR 120.50']));

        $this->post(URL::signedRoute('public.invoices.paypal', ['invoice' => $invoice->id]))
            ->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER1');

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request->hasHeader('Authorization', 'Bearer tok')
            && $request['purchase_units'][0]['amount'] === ['currency_code' => 'EUR', 'value' => '120.50']);
    }

    public function test_returning_captures_once_and_records_the_payment(): void
    {
        $invoice = $this->invoice();
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v2/checkout/orders/ORDER1/capture' => Http::sequence()
                ->push($this->capturedOrder($invoice), 201)
                ->push(['name' => 'UNPROCESSABLE_ENTITY'], 422),
            '*/v2/checkout/orders/ORDER1' => Http::response($this->capturedOrder($invoice)),
        ]);

        $this->get(route('public.paypal.return', ['token' => 'ORDER1', 'PayerID' => 'X']))->assertRedirect();
        $this->get(route('public.paypal.return', ['token' => 'ORDER1', 'PayerID' => 'X']))->assertRedirect();

        $invoice->refresh();
        $this->assertSame(1, $invoice->payments()->count());
        $this->assertSame('paid', $invoice->display_status);
        $this->assertSame('PayPal', $invoice->payments()->first()->method);
    }

    public function test_an_unfinished_capture_records_nothing(): void
    {
        $invoice = $this->invoice();
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v2/checkout/orders/ORDER2/capture' => Http::response($this->capturedOrder($invoice, 'PENDING'), 201),
        ]);

        $this->get(route('public.paypal.return', ['token' => 'ORDER2']))->assertRedirect($invoice->publicUrl());
        $this->get(route('public.paypal.return', ['token' => 'bad token!']))->assertNotFound();

        $this->assertSame(0, $invoice->payments()->count());
    }
}
