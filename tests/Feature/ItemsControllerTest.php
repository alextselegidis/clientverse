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

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_items_are_managed_and_offered_on_the_invoice_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('items.store'), ['description' => 'Managed backup', 'unit_price' => 49, 'tax_rate' => 24])
            ->assertSessionHasNoErrors();

        $item = Item::sole();

        $this->actingAs($user)->get(route('items'))->assertOk()->assertSee('Managed backup');
        $this->actingAs($user)->put(route('items.update', $item), ['description' => 'Managed backup', 'unit_price' => 59, 'tax_rate' => 24]);
        $this->assertEquals(59, $item->fresh()->unit_price);

        $this->actingAs($user)->get(route('invoices.create'))->assertSee('data-unit-price="59.00"', false);

        $this->actingAs($user)->delete(route('items.destroy', $item));
        $this->assertSame(0, Item::count());
    }
}
