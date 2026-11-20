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
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_without_a_permission_list_keeps_full_access(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))->get(route('invoices'))->assertOk();
    }

    public function test_the_http_verb_decides_which_permission_is_needed(): void
    {
        $user = User::factory()->create(['role' => 'user', 'permissions' => ['invoices.view']]);
        $invoice = Invoice::create(['status' => 'draft']);

        $this->actingAs($user)->get(route('invoices'))->assertOk();
        $this->actingAs($user)->get(route('invoices.show', $invoice))->assertOk();
        $this->actingAs($user)->post(route('invoices.payments.store', $invoice), ['amount' => 1, 'paid_on' => '2026-11-20'])->assertForbidden();
        $this->actingAs($user)->delete(route('invoices.destroy', $invoice))->assertForbidden();
        $this->actingAs($user)->get(route('tickets'))->assertForbidden();
        $this->assertNotNull($invoice->fresh());
    }

    public function test_admins_ignore_their_permission_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'permissions' => []]);

        $this->actingAs($admin)->get(route('tickets'))->assertOk();
    }

    public function test_hidden_modules_drop_out_of_search_and_the_api(): void
    {
        $user = User::factory()->create(['role' => 'user', 'permissions' => ['invoices.view']]);
        Customer::create(['name' => 'Zebra Logistics']);

        $this->actingAs($user)->get(route('search', ['q' => 'Zebra']))->assertOk()->assertDontSee('Zebra Logistics');

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/customers')->assertForbidden();
    }
}
