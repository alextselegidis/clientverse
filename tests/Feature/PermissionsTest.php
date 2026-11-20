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

    public function test_an_admin_sets_and_clears_a_users_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $form = ['name' => $user->name, 'email' => $user->email, 'role' => 'user', 'is_active' => '1'];

        $this->actingAs($admin)->get(route('setup.users.edit', $user))->assertOk();

        $this->actingAs($admin)
            ->put(route('setup.users.update', $user), $form + ['restrict_permissions' => '1', 'permissions' => ['tasks.view', 'tasks.edit']])
            ->assertSessionHasNoErrors();
        $this->assertSame(['tasks.view', 'tasks.edit'], $user->fresh()->permissions);

        $this->actingAs($admin)
            ->put(route('setup.users.update', $user), $form + ['restrict_permissions' => '1', 'permissions' => ['users.view']])
            ->assertSessionHasErrors('permissions.0');

        $this->actingAs($admin)->put(route('setup.users.update', $user), $form + ['restrict_permissions' => '0']);
        $this->assertNull($user->fresh()->permissions);
    }

    public function test_the_dashboard_and_customer_page_hide_other_modules(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        \App\Models\Sale::create(['customer_id' => $customer->id, 'name' => 'Secret deal', 'value' => 5000, 'currency' => 'EUR']);
        \App\Models\Project::create(['customer_id' => $customer->id, 'name' => 'Hidden project']);
        $user = User::factory()->create(['role' => 'user', 'permissions' => ['customers.view']]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertDontSee(['Hidden project', 'EUR 5,000.00', __('open_deals')]);
        $this->actingAs($user)->get(route('customers.show', $customer))->assertOk()
            ->assertDontSee(['Secret deal', 'Hidden project']);
    }

    public function test_the_api_does_not_include_relations_from_hidden_modules(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        \App\Models\Sale::create(['customer_id' => $customer->id, 'name' => 'Secret deal']);

        Sanctum::actingAs(User::factory()->create(['role' => 'user', 'permissions' => ['customers.view']]));
        $this->getJson('/api/v1/customers?include=sales')->assertForbidden();
        $this->postJson('/api/v1/customers/search', ['includes' => [['relation' => 'projects.milestones']]])->assertForbidden();
        $this->getJson('/api/v1/customers?include=contacts')->assertOk();

        $this->app['auth']->forgetGuards();
        Sanctum::actingAs(User::factory()->create(['role' => 'user', 'permissions' => ['customers.view', 'sales.view']]));
        $this->getJson('/api/v1/customers?include=sales')->assertOk()->assertSee('Secret deal');
    }

    public function test_conversions_need_edit_access_to_the_module_they_create_in(): void
    {
        $user = User::factory()->create(['role' => 'user', 'permissions' => ['estimates.view', 'estimates.edit', 'sales.view', 'sales.edit']]);
        $estimate = \App\Models\Estimate::create(['status' => 'sent', 'currency' => 'EUR']);
        $sale = \App\Models\Sale::create(['name' => 'Deal', 'customer_id' => Customer::create(['name' => 'A'])->id]);

        $this->actingAs($user)->post(route('estimates.convert-to-invoice', $estimate))->assertForbidden();
        $this->actingAs($user)->post(route('sales.convert-to-contract', $sale))->assertForbidden();
        $this->actingAs($user)->post(route('sales.convert-to-project', $sale))->assertForbidden();

        $this->assertSame(0, Invoice::count());
        $this->assertSame(0, \App\Models\Contract::count());
        $this->assertSame(0, \App\Models\Project::count());
    }
}
