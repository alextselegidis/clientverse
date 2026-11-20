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
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_counts_only_records_in_the_period_and_currency(): void
    {
        foreach ([['2026-11-05', 'EUR', 600], ['2026-11-20', 'USD', 999], ['2026-10-30', 'EUR', 999]] as [$date, $currency, $amount]) {
            $invoice = Invoice::create(['status' => 'sent', 'currency' => $currency, 'issue_date' => $date]);
            $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => $amount]]);
        }

        $goal = Goal::create([
            'name' => 'November billing', 'metric' => 'invoiced', 'currency' => 'EUR', 'target' => 1200,
            'start_date' => '2026-11-01', 'end_date' => '2026-11-30',
        ]);

        $this->assertSame(600.0, $goal->progress);
        $this->assertSame(50, $goal->percent);

        Customer::create(['name' => 'New one']);
        $count = Goal::create(['name' => 'Customers', 'metric' => 'new_customers', 'target' => 4, 'start_date' => today(), 'end_date' => today()]);
        $this->assertSame(25, $count->percent);

        $this->actingAs(User::factory()->create())->get(route('goals'))->assertOk()->assertSee(['November billing', 'EUR 600.00', '50%']);
    }

    public function test_a_money_goal_needs_a_currency(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('goals.store'), ['name' => 'X', 'metric' => 'collected', 'target' => 10, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31'])
            ->assertSessionHasErrors('currency');
    }
}
