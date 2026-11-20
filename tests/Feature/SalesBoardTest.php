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
}
