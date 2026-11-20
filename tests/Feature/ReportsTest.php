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
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_sum_invoiced_collected_and_outstanding_amounts(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'sent', 'currency' => 'EUR', 'issue_date' => '2026-03-10']);
        $invoice->payments()->create(['amount' => 250, 'paid_on' => '2026-04-02', 'method' => 'Bank']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 1000]]);

        $user = User::factory()->create();
        $range = ['from' => '2026-01-01', 'to' => '2026-06-30'];

        $this->actingAs($user)->get(route('reports', $range))
            ->assertOk()
            ->assertSee(['Mar 2026', 'EUR 1,000.00', 'Apr 2026', 'EUR 250.00', 'Acme', 'EUR 750.00', '1 invoice']);

        $csv = $this->actingAs($user)->get(route('reports.payments-csv', $range))->assertOk()->streamedContent();
        $this->assertStringContainsString('2026-04-02,' . $invoice->number . ',Acme,250.00,EUR,Bank', $csv);

        $this->actingAs($user)->get(route('reports', ['from' => '2026-06-30', 'to' => '2026-01-01']))->assertSessionHasErrors('to');
    }

    public function test_the_csv_neutralises_formulas(): void
    {
        $invoice = Invoice::create(['customer_id' => Customer::create(['name' => '=HYPERLINK("http://x")'])->id, 'status' => 'sent', 'currency' => 'EUR']);
        $invoice->payments()->create(['amount' => 5, 'paid_on' => '2026-04-02', 'reference' => '@SUM(1)']);

        $csv = $this->actingAs(User::factory()->create())
            ->get(route('reports.payments-csv', ['from' => '2026-01-01', 'to' => '2026-12-31']))
            ->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'@SUM(1)", $csv);
        $this->assertStringContainsString(',5.00,', $csv);
    }
}
