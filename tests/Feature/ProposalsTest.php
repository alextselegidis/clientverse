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
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProposalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_proposal_is_created_from_a_sale_and_converted_to_an_estimate_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['name' => 'Acme']);
        $sale = Sale::create(['customer_id' => $customer->id, 'name' => 'Website redesign']);

        $this->actingAs($admin)->get(route('proposals.create', ['sale_id' => $sale->id]))->assertOk()->assertSee('Website redesign');
        $this->actingAs($admin)->post(route('proposals.store'), [
            'customer_id' => $customer->id, 'sale_id' => $sale->id, 'subject' => 'Website redesign', 'content' => 'Our plan',
            'status' => 'draft', 'currency' => 'EUR', 'items' => [['description' => 'Design', 'quantity' => 1, 'unit_price' => 500]],
        ])->assertSessionHasNoErrors();

        $proposal = Proposal::firstOrFail();
        $this->assertSame('PRO-' . str_pad($proposal->id, 6, '0', STR_PAD_LEFT), $proposal->number);
        $this->actingAs($admin)->get(route('proposals.show', $proposal))->assertOk()->assertSee('Our plan');

        $this->actingAs($admin)->post(route('proposals.convert-to-estimate', $proposal));
        $this->actingAs($admin)->post(route('proposals.convert-to-estimate', $proposal));

        $estimate = $proposal->fresh()->estimate;
        $this->assertSame(1, \App\Models\Estimate::count());
        $this->assertEquals(500, $estimate->total);
    }

    public function test_the_customer_accepts_a_sent_proposal_through_its_link_once(): void
    {
        $proposal = Proposal::create(['subject' => 'Retainer', 'content' => 'Monthly support', 'status' => 'sent']);
        $draft = Proposal::create(['subject' => 'Secret', 'status' => 'draft']);
        $url = $proposal->publicUrl();

        $this->get($draft->publicUrl())->assertNotFound();
        $this->get($url)->assertOk()->assertSee('Monthly support')->assertSee(__('accept_proposal'));
        $this->from($url)->post($url, ['decision' => 'accepted'])->assertRedirect($url);
        $this->assertSame('accepted', $proposal->fresh()->status);
        $this->post($url, ['decision' => 'declined'])->assertNotFound();
    }

    public function test_proposals_need_their_own_permission(): void
    {
        $user = User::factory()->create(['permissions' => ['estimates.view']]);

        $this->actingAs($user)->get(route('proposals'))->assertForbidden();
    }
}
