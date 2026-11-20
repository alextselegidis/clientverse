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
}
