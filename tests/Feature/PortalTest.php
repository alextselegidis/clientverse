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

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    private function contact(array $attributes = []): Contact
    {
        $customer = Customer::create(['name' => 'Acme']);
        $contact = new Contact(['customer_id' => $customer->id, 'first_name' => 'Jane', 'email' => 'jane@acme.test',
            'has_portal_access' => true] + $attributes);
        $contact->password = 'secret-pass';
        $contact->save();

        return $contact;
    }

    public function test_a_contact_signs_in_and_sees_only_their_customers_sent_documents(): void
    {
        $contact = $this->contact();
        Invoice::create(['customer_id' => $contact->customer_id, 'status' => 'sent', 'number' => 'INV-MINE']);
        Invoice::create(['customer_id' => $contact->customer_id, 'status' => 'draft', 'number' => 'INV-DRAFT']);
        Invoice::create(['customer_id' => Customer::create(['name' => 'Other'])->id, 'status' => 'sent', 'number' => 'INV-OTHER']);

        $this->get(route('portal'))->assertRedirect(route('portal.login'));
        $this->post(route('portal.login.perform'), ['email' => 'jane@acme.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post(route('portal.login.perform'), ['email' => 'jane@acme.test', 'password' => 'secret-pass'])
            ->assertRedirect(route('portal'));

        $this->get(route('portal'))->assertOk()->assertSee('INV-MINE')->assertDontSee('INV-DRAFT')->assertDontSee('INV-OTHER');
        $this->assertGuest('web');
    }

    public function test_revoking_portal_access_signs_the_contact_out(): void
    {
        $contact = $this->contact();

        $this->actingAs($contact, 'contact')->get(route('portal'))->assertOk();

        $contact->update(['has_portal_access' => false]);

        $this->actingAs($contact->fresh(), 'contact')->get(route('portal'))->assertRedirect(route('portal.login'));
    }

    public function test_a_contact_opens_and_replies_to_tickets_but_cannot_read_another_customers(): void
    {
        $contact = $this->contact();
        $foreign = Ticket::create(['customer_id' => Customer::create(['name' => 'Other'])->id, 'subject' => 'Theirs']);

        $this->actingAs($contact, 'contact')
            ->post(route('portal.tickets.store'), ['subject' => 'Broken login', 'body' => 'Help'])
            ->assertSessionHas('success');
        $ticket = Ticket::where('subject', 'Broken login')->firstOrFail();
        $this->assertSame($contact->id, $ticket->contact_id);

        $this->actingAs($contact, 'contact')->post(route('portal.tickets.replies.store', $ticket), ['body' => 'Still broken']);
        $this->assertSame(2, $ticket->replies()->whereNotNull('contact_id')->count());
        $this->actingAs($contact, 'contact')->get(route('portal.tickets.show', $ticket))->assertOk()->assertSee('Still broken');
        $this->actingAs($contact, 'contact')->get(route('portal.tickets.create'))->assertOk();

        $this->actingAs($contact, 'contact')->get(route('portal.tickets.show', $foreign))->assertNotFound();
        $this->actingAs($contact, 'contact')->post(route('portal.tickets.replies.store', $foreign), ['body' => 'x'])->assertNotFound();
    }

    public function test_staff_set_a_portal_password_and_a_blank_one_keeps_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['name' => 'Acme']);

        $this->actingAs($admin)->post(route('customers.contacts.store', $customer), [
            'first_name' => 'Jane', 'email' => 'jane@acme.test', 'has_portal_access' => 1, 'password' => '',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post(route('customers.contacts.store', $customer), [
            'first_name' => 'Jane', 'email' => 'jane@acme.test', 'has_portal_access' => 1, 'password' => 'secret-pass',
        ])->assertSessionHasNoErrors();
        $contact = Contact::firstOrFail();
        $hash = $contact->password;

        $this->actingAs($admin)->put(route('customers.contacts.update', [$customer, $contact]), [
            'first_name' => 'Jane', 'email' => 'jane@acme.test', 'has_portal_access' => 1, 'password' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame($hash, $contact->fresh()->password);
        $this->assertArrayNotHasKey('password', $contact->fresh()->toArray());
    }
}
