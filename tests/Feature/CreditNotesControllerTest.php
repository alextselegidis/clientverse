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

use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditNotesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_credit_note_is_issued_and_applied_to_a_matching_invoice_only(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme']);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 300]]);
        $foreign = Invoice::create(['customer_id' => Customer::create(['name' => 'Other'])->id, 'status' => 'sent', 'currency' => 'EUR']);

        $this->actingAs($user)->get(route('credit-notes.create'))->assertOk();
        $this->actingAs($user)
            ->post(route('credit-notes.store'), [
                'customer_id' => $customer->id,
                'status' => 'issued',
                'currency' => 'EUR',
                'items' => [['description' => 'Refund', 'quantity' => 1, 'unit_price' => 50, 'tax_rate' => 0]],
            ])
            ->assertSessionHasNoErrors();

        $credit = CreditNote::sole();

        $this->actingAs($user)->get(route('credit-notes'))->assertOk()->assertSee($credit->number);
        $this->actingAs($user)->get(route('credit-notes.show', $credit))->assertOk()->assertSee($invoice->number);
        $this->actingAs($user)->get(route('credit-notes.print', $credit))->assertOk()->assertSee('Refund');

        $this->actingAs($user)->post(route('credit-notes.apply', $credit), ['invoice_id' => $foreign->id])->assertSessionHas('error');
        $this->assertNull($credit->fresh()->invoice_id);

        $this->actingAs($user)->post(route('credit-notes.apply', $credit), ['invoice_id' => $invoice->id]);
        $this->assertEquals(250.00, $invoice->fresh()->balance);
        $this->actingAs($user)->get(route('invoices.show', $invoice))->assertSee($credit->number);

        $this->actingAs($user)->delete(route('credit-notes.unapply', $credit));
        $this->assertEquals(300.00, $invoice->fresh()->balance);
    }
}
