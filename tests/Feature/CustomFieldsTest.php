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
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_an_admin_defines_a_dropdown_from_lines(): void
    {
        $this->post(route('setup.custom-fields.store'), [
            'applies_to' => 'customers', 'label' => 'Tier', 'type' => 'select', 'options' => "Gold\r\n\r\nSilver\nGold",
            'is_required' => '1',
        ])->assertRedirect(route('setup.custom-fields'));

        $field = CustomField::firstOrFail();
        $this->assertSame(['Gold', 'Silver'], $field->options);
        $this->assertTrue($field->is_required);

        $this->post(route('setup.custom-fields.store'), ['applies_to' => 'customers', 'label' => 'X', 'type' => 'select', 'options' => ''])
            ->assertSessionHasErrors('options');
    }

    public function test_records_save_validate_and_show_their_values(): void
    {
        $tier = CustomField::create(['applies_to' => 'customers', 'label' => 'Tier', 'type' => 'select', 'options' => ['Gold', 'Silver'], 'is_required' => true]);
        $vip = CustomField::create(['applies_to' => 'customers', 'label' => 'VIP', 'type' => 'checkbox']);
        $seats = CustomField::create(['applies_to' => 'customers', 'label' => 'Seats', 'type' => 'number']);
        CustomField::create(['applies_to' => 'projects', 'label' => 'Budget code', 'type' => 'text', 'is_required' => true]);

        $this->post(route('customers.store'), ['name' => 'Acme', 'custom_values' => [$tier->id => 'Bronze']])
            ->assertSessionHasErrors('custom_values.' . $tier->id);

        $this->post(route('customers.store'), ['name' => 'Acme', 'custom_values' => [
            $tier->id => 'Gold', $vip->id => '1', $seats->id => '12', 999 => 'forged',
        ]])->assertSessionHasNoErrors();

        $customer = Customer::firstOrFail();
        $this->assertSame([$tier->id => 'Gold', $vip->id => true, $seats->id => '12'], $customer->custom_values);

        // Unticking sends the hidden 0; a field left out of the request keeps its value.
        $customer->update(['custom_values' => [$vip->id => '0']]);
        $this->assertSame([$tier->id => 'Gold', $vip->id => false, $seats->id => '12'], $customer->fresh()->custom_values);

        $this->get(route('customers.show', $customer))->assertSee('Tier')->assertSee('Gold')->assertDontSee('Budget code');
        $this->get(route('customers.edit', $customer))->assertOk()->assertSee('custom_values[' . $seats->id . ']', false);
    }
}
