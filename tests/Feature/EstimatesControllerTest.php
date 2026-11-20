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
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimatesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_estimate_is_created_shown_printed_and_converted(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme']);

        $this->actingAs($user)->get(route('estimates.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('estimates.store'), [
                'customer_id' => $customer->id,
                'status' => 'sent',
                'currency' => 'EUR',
                'issue_date' => '2026-11-01',
                'expiry_date' => '2026-11-30',
                'items' => [['description' => 'Migration', 'quantity' => 1, 'unit_price' => 900, 'tax_rate' => 0]],
            ])
            ->assertSessionHasNoErrors();

        $estimate = Estimate::sole();

        $this->actingAs($user)->get(route('estimates', ['status' => 'sent', 'sort' => 'total']))->assertOk()->assertSee($estimate->number);
        $this->actingAs($user)->get(route('estimates.show', $estimate))->assertOk()->assertSee('Migration');
        $this->actingAs($user)->get(route('estimates.edit', $estimate))->assertOk()->assertSee('Migration');
        $this->actingAs($user)->get(route('estimates.print', $estimate))->assertOk()->assertSee('Migration');

        $this->actingAs($user)->post(route('estimates.convert-to-invoice', $estimate))->assertRedirect();
        $this->actingAs($user)->post(route('estimates.convert-to-invoice', $estimate))->assertRedirect();

        $this->assertSame(1, Invoice::count());
        $this->actingAs($user)->get(route('estimates.show', $estimate))->assertSee(Invoice::sole()->number);
    }
}
