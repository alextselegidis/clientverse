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

class ApiModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_ticket_is_validated_and_keeps_its_message_as_the_first_reply(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme', 'type' => 'company', 'status' => 'active']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/tickets', ['subject' => 'Broken'])->assertUnprocessable();

        $id = $this->postJson('/api/v1/tickets', [
            'subject' => 'Broken',
            'customer_id' => $customer->id,
            'priority' => 'high',
            'body' => 'It does not start',
            'status' => 'closed',
        ])->assertCreated()->json('data.id');

        $this->getJson("/api/v1/tickets/{$id}?include=replies")
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.replies.0.body', 'It does not start')
            ->assertJsonPath('data.replies.0.user_id', $user->id);

        $this->patchJson("/api/v1/tickets/{$id}", ['status' => 'closed'])->assertJsonPath('data.status', 'closed');
    }

    public function test_an_update_checks_only_the_fields_it_sends(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/v1/items', ['description' => 'Hour', 'unit_price' => 80, 'tax_rate' => 20])
            ->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/items/{$id}", ['unit_price' => 90])->assertOk()->assertJsonPath('data.description', 'Hour');
        $this->patchJson("/api/v1/items/{$id}", ['tax_rate' => 150])->assertUnprocessable();
    }

    public function test_invoices_are_read_only(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $invoice = Invoice::create(['status' => 'draft']);

        $this->getJson('/api/v1/invoices')->assertOk()->assertJsonPath('data.0.id', $invoice->id);
        $this->postJson('/api/v1/invoices', ['status' => 'sent'])->assertStatus(405);
        $this->deleteJson("/api/v1/invoices/{$invoice->id}")->assertStatus(405);
    }

    public function test_an_include_from_another_module_needs_its_permission(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'user', 'permissions' => ['expenses.view']]));

        $this->getJson('/api/v1/expenses')->assertOk();
        $this->getJson('/api/v1/expenses?include=invoice')->assertForbidden();
    }
}
