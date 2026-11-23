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

use App\Models\Invoice;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_month_shows_due_dates_the_user_may_see(): void
    {
        Task::create(['title' => 'File VAT return', 'due_date' => '2026-12-15']);
        Task::create(['title' => 'Already done', 'status' => 'done', 'due_date' => '2026-12-16']);
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR', 'due_date' => '2026-12-20']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 10]]);

        $this->actingAs(User::factory()->create())
            ->get(route('calendar', ['month' => '2026-12']))
            ->assertOk()
            ->assertSee(['December 2026', 'File VAT return', $invoice->number])
            ->assertDontSee('Already done');

        $this->actingAs(User::factory()->create(['role' => 'user', 'permissions' => ['tasks.view']]))
            ->get(route('calendar', ['month' => '2026-12']))
            ->assertSee('File VAT return')
            ->assertDontSee($invoice->number);

        $this->actingAs(User::factory()->create())->get(route('calendar', ['month' => 'nope']))->assertSessionHasErrors('month');
    }

    public function test_events_filter_by_search_and_type(): void
    {
        Task::create(['title' => 'File VAT return', 'due_date' => '2026-12-15']);
        Task::create(['title' => 'Call the bank', 'due_date' => '2026-12-16']);
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR', 'due_date' => '2026-12-20']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 10]]);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('calendar', ['month' => '2026-12', 'q' => 'vat']))
            ->assertSee('File VAT return')
            ->assertDontSee(['Call the bank', $invoice->number]);

        $this->actingAs($user)->get(route('calendar', ['month' => '2026-12', 'type' => 'invoices']))
            ->assertSee($invoice->number)
            ->assertDontSee(['File VAT return', 'Call the bank']);
    }
}
