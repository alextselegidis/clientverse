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
use App\Models\Estimate;
use App\Models\Invoice;
use App\Notifications\DocumentEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RecurringAndRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_recurring_invoice_catches_up_once_per_missed_date_and_emails_each_copy(): void
    {
        Notification::fake();
        $this->travelTo('2026-03-15');

        $customer = Customer::create(['name' => 'Acme', 'email' => 'billing@acme.test']);
        $template = Invoice::create([
            'customer_id' => $customer->id, 'status' => 'sent', 'currency' => 'EUR',
            'issue_date' => '2026-01-01', 'due_date' => '2026-01-15',
            'recurring_interval' => 'month', 'next_recurring_date' => '2026-02-01',
        ]);
        $template->syncItems([['description' => 'Hosting', 'quantity' => 1, 'unit_price' => 20]]);

        $this->artisan('invoices:recur')->assertSuccessful();
        $this->artisan('invoices:recur')->assertSuccessful();

        $copies = Invoice::where('recurring_parent_id', $template->id)->orderBy('issue_date')->get();
        $this->assertSame(['2026-02-01', '2026-03-01'], $copies->map(fn ($i) => $i->issue_date->toDateString())->all());
        $this->assertSame('2026-02-15', $copies[0]->due_date->toDateString());
        $this->assertEquals(20, $copies[0]->total);
        $this->assertNull($copies[0]->recurring_interval);
        $this->assertSame('2026-04-01', $template->fresh()->next_recurring_date->toDateString());
        Notification::assertSentOnDemandTimes(DocumentEmail::class, 2);
    }

    public function test_a_draft_or_cancelled_recurring_invoice_is_paused(): void
    {
        Invoice::create(['status' => 'cancelled', 'recurring_interval' => 'week', 'next_recurring_date' => today()->subWeek()]);

        $this->artisan('invoices:recur')->assertSuccessful();

        $this->assertSame(1, Invoice::count());
    }

    public function test_reminders_are_off_until_switched_on_then_sent_once_per_window(): void
    {
        Notification::fake();
        $customer = Customer::create(['name' => 'Acme', 'email' => 'billing@acme.test']);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'sent', 'due_date' => today()->subDay()]);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]]);
        $estimate = Estimate::create(['customer_id' => $customer->id, 'status' => 'sent', 'expiry_date' => today()->addDays(2)]);
        Contract::create(['customer_id' => $customer->id, 'title' => 'Support', 'status' => 'active', 'end_date' => today()->addDays(5)]);
        Contract::create(['customer_id' => $customer->id, 'title' => 'Later', 'status' => 'active', 'end_date' => today()->addDays(30)]);

        $this->artisan('reminders:send')->assertSuccessful();
        Notification::assertNothingSent();

        setting(['send_reminders' => '1']);
        $this->artisan('reminders:send')->assertSuccessful();
        $this->artisan('reminders:send')->assertSuccessful();

        Notification::assertSentOnDemandTimes(DocumentEmail::class, 3);
        $this->assertNotNull($invoice->fresh()->reminded_at);

        // A week on the invoice is still overdue and gets a second reminder.
        $this->travel(8)->days();
        $this->artisan('reminders:send')->assertSuccessful();
        Notification::assertSentOnDemandTimes(DocumentEmail::class, 4);

        // A new expiry date earns a new reminder.
        $estimate->update(['expiry_date' => today()->addDay()]);
        $this->assertNull($estimate->fresh()->reminded_at);
    }
}
