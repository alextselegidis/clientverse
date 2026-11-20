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
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\DocumentEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DocumentEmailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_invoice_is_emailed_with_the_admins_template_and_marked_sent(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['name' => 'Jane', 'email' => 'jane@acme.test']);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'draft', 'currency' => 'EUR']);

        $this->actingAs($admin)->get(route('setup.billing'))->assertOk();
        $this->actingAs($admin)
            ->put(route('setup.billing.update'), ['company_name' => 'Northwind', 'invoice_email_subject' => 'Bill {number} for {customer}'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('invoices.email', $invoice))->assertSessionHas('success');

        $this->assertSame('sent', $invoice->fresh()->status);
        Notification::assertSentOnDemand(DocumentEmail::class, function (DocumentEmail $mail, $channels, $notifiable) use ($invoice) {
            return $notifiable->routes['mail'] === 'jane@acme.test'
                && $mail->subject === 'Bill ' . $invoice->number . ' for Jane'
                && str_contains($mail->body, $invoice->publicUrl())
                && str_contains($mail->body, 'Northwind');
        });
    }

    public function test_nothing_is_sent_or_changed_without_a_customer_email(): void
    {
        Notification::fake();

        $contract = Contract::create(['customer_id' => Customer::create(['name' => 'No Mail'])->id, 'title' => 'NDA', 'status' => 'draft']);
        $invoice = Invoice::create(['customer_id' => $contract->customer_id, 'status' => 'draft']);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('contracts.email', $contract))->assertSessionHas('error');
        $this->actingAs($user)->post(route('invoices.email', $invoice))->assertSessionHas('error');

        $this->assertSame('draft', $invoice->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_a_failed_send_leaves_the_draft_alone_and_says_so(): void
    {
        Notification::shouldReceive('route->notify')->andThrow(new \RuntimeException('SMTP down'));

        $invoice = Invoice::create(['customer_id' => Customer::create(['name' => 'Jane', 'email' => 'jane@acme.test'])->id, 'status' => 'draft']);

        $this->actingAs(User::factory()->create())
            ->post(route('invoices.email', $invoice))
            ->assertSessionHas('error', __('email_failed_message'));

        $this->assertSame('draft', $invoice->fresh()->status);
    }
}
