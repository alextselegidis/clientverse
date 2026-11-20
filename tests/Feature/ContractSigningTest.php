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

use App\Models\Contract;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractSigningTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_customer_signs_through_the_signed_link_only(): void
    {
        $contract = Contract::create([
            'customer_id' => Customer::create(['name' => 'Acme'])->id,
            'title' => 'Support agreement',
            'status' => 'draft',
        ]);
        $url = $contract->signingUrl();

        $this->get(route('public.contracts.show', $contract))->assertForbidden();
        $this->post(route('public.contracts.sign', $contract), ['signer_name' => 'Mallory', 'agree' => '1'])->assertForbidden();

        $this->get($url)->assertOk()->assertSee('Support agreement');
        $this->from($url)->post($url, ['signer_name' => 'Jane Doe'])->assertSessionHasErrors('agree');

        $this->from($url)->post($url, ['signer_name' => 'Jane Doe', 'agree' => '1'])->assertRedirect($url);

        $contract->refresh();
        $this->assertSame('Jane Doe', $contract->signer_name);
        $this->assertSame('active', $contract->status);
        $this->assertNotNull($contract->signed_at);

        $this->from($url)->post($url, ['signer_name' => 'Someone Else', 'agree' => '1']);
        $this->assertSame('Jane Doe', $contract->fresh()->signer_name);

        $this->actingAs(User::factory()->create())->get(route('contracts.show', $contract))->assertOk()->assertSee('Jane Doe');
    }
}
