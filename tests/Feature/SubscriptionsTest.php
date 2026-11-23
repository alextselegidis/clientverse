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

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(): Subscription
    {
        setting(['stripe_secret_key' => 'sk_test_123', 'stripe_webhook_secret' => 'whsec_test']);

        return Subscription::create([
            'customer_id' => Customer::create(['name' => 'Acme', 'email' => 'billing@acme.test'])->id,
            'name' => 'Care plan', 'currency' => 'EUR', 'amount' => 50, 'tax_rate' => 24, 'interval' => 'month',
        ]);
    }

    private function webhook(array $event)
    {
        $payload = json_encode($event);
        $time = time();
        $signature = hash_hmac('sha256', $time . '.' . $payload, 'whsec_test');

        return $this->call('POST', route('api.stripe.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}",
        ], $payload);
    }

    private function paidInvoice(Subscription $subscription, string $id = 'in_1'): array
    {
        return ['type' => 'invoice.paid', 'data' => ['object' => [
            'id' => $id, 'amount_paid' => 6200, 'currency' => 'eur', 'customer' => 'cus_1',
            'parent' => ['subscription_details' => ['subscription' => 'sub_1', 'metadata' => ['subscription_id' => $subscription->id]]],
            'lines' => ['data' => [['period' => ['end' => now()->addMonth()->timestamp]]]],
        ]]];
    }

    public function test_the_customer_subscribes_through_stripe_checkout_for_the_gross_price(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['url' => 'https://checkout.stripe.com/c/sub'])]);
        $subscription = $this->subscription();

        $this->get($subscription->publicUrl())->assertOk()->assertSee('EUR 62.00')->assertSee(__('subscribe'));
        $this->post(URL::signedRoute('public.subscriptions.subscribe', ['subscription' => $subscription->id]))
            ->assertRedirect('https://checkout.stripe.com/c/sub');
        $this->post(route('public.subscriptions.subscribe', $subscription))->assertForbidden();

        Http::assertSent(fn ($request) => $request['mode'] === 'subscription'
            && $request['line_items'][0]['price_data']['unit_amount'] == 6200
            && $request['line_items'][0]['price_data']['recurring']['interval'] === 'month'
            && $request['subscription_data']['metadata']['subscription_id'] == $subscription->id);
        $this->assertSame('pending', $subscription->fresh()->status);
    }

    public function test_each_paid_period_is_booked_once_as_a_paid_invoice(): void
    {
        $subscription = $this->subscription();

        // The first invoice may land before checkout.session.completed; it still finds its subscription.
        $this->webhook($this->paidInvoice($subscription))->assertNoContent();
        $this->webhook($this->paidInvoice($subscription))->assertNoContent();
        $this->webhook(['type' => 'checkout.session.completed', 'data' => ['object' => [
            'mode' => 'subscription', 'subscription' => 'sub_1', 'customer' => 'cus_1',
            'metadata' => ['subscription_id' => $subscription->id],
        ]]])->assertNoContent();
        $this->webhook($this->paidInvoice($subscription, 'in_2'))->assertNoContent();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('sub_1', $subscription->stripe_subscription_id);
        $this->assertSame(2, $subscription->invoices()->count());
        $this->assertTrue($subscription->invoices->every(fn (Invoice $invoice) => $invoice->display_status === 'paid'));

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('subscriptions.show', $subscription))->assertOk()->assertSee($subscription->invoices->first()->number);
        $this->get(route('subscriptions.edit', $subscription))->assertForbidden();
    }

    public function test_failed_payments_and_stripe_cancellations_are_followed(): void
    {
        $subscription = $this->subscription();
        $subscription->forceFill(['status' => 'active', 'stripe_subscription_id' => 'sub_1'])->save();

        $this->webhook(['type' => 'invoice.payment_failed', 'data' => ['object' => [
            'id' => 'in_9', 'subscription' => 'sub_1', 'amount_paid' => 0,
        ]]]);
        $this->assertSame('past_due', $subscription->fresh()->status);

        $this->webhook(['type' => 'customer.subscription.deleted', 'data' => ['object' => ['id' => 'sub_1', 'status' => 'canceled']]]);
        $this->assertSame('canceled', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->canceled_at);
    }

    public function test_staff_cancel_at_stripe_first_and_keep_it_active_when_stripe_refuses(): void
    {
        $subscription = $this->subscription();
        $subscription->forceFill(['status' => 'active', 'stripe_subscription_id' => 'sub_1'])->save();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Http::fake(['api.stripe.com/v1/subscriptions/sub_1' => Http::sequence()
            ->push(['error' => ['code' => 'api_error']], 500)
            ->push(['status' => 'canceled'])]);

        $this->post(route('subscriptions.cancel', $subscription))->assertSessionHas('error');
        $this->assertSame('active', $subscription->fresh()->status);

        $this->post(route('subscriptions.cancel', $subscription))->assertSessionHas('success');
        $this->assertSame('canceled', $subscription->fresh()->status);
    }
}
