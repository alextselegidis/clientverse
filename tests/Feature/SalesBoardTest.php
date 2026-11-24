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
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_board_lists_deals_by_stage_and_moves_them(): void
    {
        $user = User::factory()->create();
        $sale = Sale::create(['name' => 'Cloud migration', 'customer_id' => Customer::create(['name' => 'Acme'])->id]);

        $this->actingAs($user)->get(route('sales.board'))->assertOk()->assertSee('Cloud migration');

        $this->actingAs($user)->patch(route('sales.stage', $sale), ['stage' => 'qualified'])->assertRedirect(route('sales.board'));
        $this->assertSame('qualified', $sale->fresh()->stage);

        $this->actingAs($user)->patch(route('sales.stage', $sale), ['stage' => 'nonsense'])->assertSessionHasErrors('stage');
    }

    public function test_old_closed_deals_leave_the_board(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        $old = Sale::create(['name' => 'Won long ago', 'stage' => 'won', 'customer_id' => $customer->id]);
        $old->forceFill(['updated_at' => now()->subDays(200)])->saveQuietly();
        Sale::create(['name' => 'Won last week', 'stage' => 'won', 'customer_id' => $customer->id]);

        $this->actingAs(User::factory()->create())->get(route('sales.board'))
            ->assertSee('Won last week')->assertDontSee('Won long ago');
    }

    public function test_a_deal_counts_toward_monthly_sales_in_the_month_it_is_won(): void
    {
        $this->travelTo('2026-11-15 10:00');
        $customer = Customer::create(['name' => 'Acme']);
        $sale = Sale::create(['name' => 'Firewall', 'customer_id' => $customer->id, 'value' => 900, 'currency' => 'EUR',
            'stage' => 'qualified', 'expected_close_date' => '2026-12-20']);
        $this->assertNull($sale->won_at);

        $sale->update(['stage' => 'won']);
        $this->assertSame('2026-11-15', $sale->won_at->toDateString());

        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertSee(format_currency(900, 'EUR'));

        // Editing a won deal keeps its date; reopening it clears it.
        $this->travelTo('2026-12-02');
        $sale->update(['name' => 'Firewall upgrade']);
        $this->assertSame('2026-11-15', $sale->fresh()->won_at->toDateString());
        $sale->update(['stage' => 'proposal_sent']);
        $this->assertNull($sale->fresh()->won_at);
    }

    public function test_list_pages_sort_by_created_and_modified(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        $this->travelTo('2026-01-01');
        Sale::create(['name' => 'Older deal', 'customer_id' => $customer->id]);
        $this->travelTo('2026-06-01');
        Sale::create(['name' => 'Newer deal', 'customer_id' => $customer->id]);

        $this->actingAs(User::factory()->create())->get(route('sales', ['sort' => 'created_at', 'direction' => 'asc']))
            ->assertOk()->assertSeeInOrder(['Older deal', 'Newer deal'])->assertSee('Jan 01, 2026');
        $this->get(route('sales', ['sort' => 'updated_at', 'direction' => 'desc']))->assertSeeInOrder(['Newer deal', 'Older deal']);
    }
}
