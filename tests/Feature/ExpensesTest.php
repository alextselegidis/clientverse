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
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_billable_expense_is_billed_to_its_customer_once(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme']);

        $this->actingAs($user)->get(route('expenses.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('expenses.store'), [
                'expense_date' => '2026-11-18',
                'category' => 'Travel',
                'description' => 'Train to client site',
                'amount' => 84.5,
                'currency' => 'EUR',
                'billable' => '1',
                'customer_id' => $customer->id,
            ])
            ->assertSessionHasNoErrors();

        $expense = Expense::sole();

        $this->actingAs($user)->get(route('expenses', ['category' => 'Travel']))->assertOk()->assertSee('Train to client site');
        $this->actingAs($user)->get(route('expenses.edit', $expense))->assertOk()->assertSee(__('bill_to_customer'));

        $this->actingAs($user)->post(route('expenses.bill', $expense))->assertRedirect();
        $this->actingAs($user)->post(route('expenses.bill', $expense))->assertRedirect();

        $invoice = Invoice::sole();
        $this->assertEquals(84.50, $invoice->total);
        $this->assertTrue($expense->fresh()->invoice->is($invoice));
    }

    public function test_a_billable_expense_needs_a_customer(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('expenses.store'), [
                'expense_date' => '2026-11-18',
                'description' => 'Hotel',
                'amount' => 120,
                'currency' => 'EUR',
                'billable' => '1',
            ])
            ->assertSessionHasErrors('customer_id');
    }
}
