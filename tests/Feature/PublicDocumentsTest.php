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

use App\Models\Estimate;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_sent_invoice_opens_through_its_link_and_a_draft_does_not(): void
    {
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Hosting', 'quantity' => 1, 'unit_price' => 20]]);
        $draft = Invoice::create(['status' => 'draft']);

        $this->get($invoice->publicUrl())->assertOk()->assertSee('Hosting')->assertDontSee(__('back'));
        $this->get(route('public.invoices.show', $invoice))->assertForbidden();
        $this->get($draft->publicUrl())->assertNotFound();
    }

    public function test_the_customer_accepts_a_sent_estimate_once(): void
    {
        $estimate = Estimate::create(['status' => 'sent', 'currency' => 'EUR']);
        $url = $estimate->publicUrl();

        $this->get($url)->assertOk()->assertSee(__('accept_estimate'));
        $this->from($url)->post($url, ['decision' => 'accepted'])->assertRedirect($url);
        $this->assertSame('accepted', $estimate->fresh()->status);

        $this->get($url)->assertOk()->assertDontSee(__('accept_estimate'));
        $this->post($url, ['decision' => 'declined'])->assertNotFound();
        $this->assertSame('accepted', $estimate->fresh()->status);
    }
}
