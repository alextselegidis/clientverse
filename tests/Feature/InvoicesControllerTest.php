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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_invoice_is_created_with_its_lines_and_every_screen_renders(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme']);

        $this->actingAs($user)->get(route('invoices.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('invoices.store'), [
                'customer_id' => $customer->id,
                'status' => 'sent',
                'currency' => 'EUR',
                'issue_date' => '2026-11-01',
                'due_date' => '2026-12-01',
                'items' => [
                    ['description' => 'Consulting', 'quantity' => 3, 'unit_price' => 120, 'tax_rate' => 10],
                    ['description' => '', 'quantity' => 1, 'unit_price' => ''],
                ],
            ])
            ->assertSessionHasNoErrors();

        $invoice = Invoice::sole();
        $this->assertEquals(396.00, $invoice->total);
        $this->assertSame(1, $invoice->items()->count());

        $this->actingAs($user)->get(route('invoices'))->assertOk()->assertSee($invoice->number);
        $this->actingAs($user)->get(route('invoices', ['status' => 'unpaid', 'sort' => 'total']))->assertOk()->assertSee($invoice->number);
        $this->actingAs($user)->get(route('invoices.show', $invoice))->assertOk()->assertSee('Consulting');
        $this->actingAs($user)->get(route('invoices.edit', $invoice))->assertOk()->assertSee('Consulting');
    }

    public function test_the_due_date_cannot_come_before_the_issue_date(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('invoices.store'), [
                'customer_id' => Customer::create(['name' => 'Acme'])->id,
                'status' => 'draft',
                'currency' => 'EUR',
                'issue_date' => '2026-11-10',
                'due_date' => '2026-11-01',
            ])
            ->assertSessionHasErrors('due_date');

        $this->assertSame(0, Invoice::count());
    }

    public function test_payments_move_the_balance_and_stay_on_their_own_invoice(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 500]]);
        $other = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);

        $this->actingAs($user)
            ->post(route('invoices.payments.store', $invoice), ['amount' => 200, 'paid_on' => '2026-11-20'])
            ->assertSessionHasNoErrors();

        $invoice->refresh();
        $this->assertEquals(300.00, $invoice->balance);
        $this->assertSame('partially_paid', $invoice->display_status);
        $this->actingAs($user)->get(route('invoices.show', $invoice))->assertOk()->assertSee('200.00');

        $payment = $invoice->payments()->sole();

        $this->actingAs($user)
            ->delete(route('invoices.payments.destroy', [$other, $payment]))
            ->assertNotFound();

        $this->actingAs($user)->delete(route('invoices.payments.destroy', [$invoice, $payment]));

        $this->assertEquals(500.00, $invoice->fresh()->balance);
    }

    public function test_a_payment_must_be_positive(): void
    {
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);

        $this->actingAs(User::factory()->create())
            ->post(route('invoices.payments.store', $invoice), ['amount' => -5, 'paid_on' => '2026-11-20'])
            ->assertSessionHasErrors('amount');
    }
}
