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
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tickets_are_filed_and_filtered_by_department(): void
    {
        $sales = Department::create(['name' => 'Sales']);
        $support = Department::create(['name' => 'Support']);
        $customer = Customer::create(['name' => 'Acme']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('tickets.store'), [
            'subject' => 'Quote please', 'customer_id' => $customer->id, 'priority' => 'normal',
            'department_id' => $sales->id, 'body' => 'Hi',
        ])->assertRedirect();
        Ticket::create(['customer_id' => $customer->id, 'subject' => 'Broken', 'department_id' => $support->id]);

        $this->get(route('tickets', ['department_id' => $sales->id]))
            ->assertSee('Quote please')
            ->assertDontSee('Broken');
    }

    public function test_a_contact_picks_a_department_and_deleting_it_keeps_the_ticket(): void
    {
        $department = Department::create(['name' => 'Billing']);
        $customer = Customer::create(['name' => 'Acme']);
        $contact = new Contact(['customer_id' => $customer->id, 'first_name' => 'Jo', 'email' => 'jo@acme.test',
            'has_portal_access' => true]);
        $contact->password = 'secret-pass';
        $contact->save();

        $this->actingAs($contact, 'contact')
            ->post(route('portal.tickets.store'), ['subject' => 'Refund', 'department_id' => $department->id, 'body' => 'Hi'])
            ->assertRedirect();

        $ticket = Ticket::firstOrFail();
        $this->assertSame($department->id, $ticket->department_id);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('setup.departments.destroy', $department->id));

        $this->assertNull($ticket->fresh()->department_id);
    }
}
