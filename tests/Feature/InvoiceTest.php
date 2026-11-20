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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(array $attributes, array $lines = [], array $payments = []): Invoice
    {
        $invoice = Invoice::create($attributes + ['status' => 'sent']);

        foreach ($payments as $amount) {
            $invoice->payments()->create(['amount' => $amount, 'paid_on' => today()]);
        }

        $invoice->syncItems($lines);

        return $invoice->fresh();
    }

    public function test_totals_include_tax_and_blank_lines_are_dropped(): void
    {
        $invoice = $this->invoice([], [
            ['description' => 'Design', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 24],
            ['description' => '', 'quantity' => 5, 'unit_price' => 999],
            ['description' => 'Hosting', 'quantity' => 1, 'unit_price' => 50],
        ], [100]);

        $this->assertSame(2, $invoice->items()->count());
        $this->assertEquals(298.00, $invoice->total);
        $this->assertEquals(198.00, $invoice->balance);
        $this->assertSame('INV-'.str_pad($invoice->id, 6, '0', STR_PAD_LEFT), $invoice->number);
    }

    public function test_the_status_filter_agrees_with_the_displayed_status(): void
    {
        $line = [['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]];
        $past = today()->subDay();
        $future = today()->addDay();

        $cases = [
            'draft' => $this->invoice(['status' => 'draft'], $line),
            'cancelled' => $this->invoice(['status' => 'cancelled'], $line),
            'paid' => $this->invoice(['due_date' => $past], $line, [100]),
            'overpaid' => $this->invoice([], $line, [150]),
            'overdue' => $this->invoice(['due_date' => $past], $line, [40]),
            'partially_paid' => $this->invoice(['due_date' => $future], $line, [40]),
            'unpaid' => $this->invoice([], $line),
            'empty_and_late' => $this->invoice(['due_date' => $past]),
            'due_today' => $this->invoice(['due_date' => today()], $line),
        ];

        $expected = [
            'draft' => 'draft', 'cancelled' => 'cancelled', 'paid' => 'paid', 'overpaid' => 'paid',
            'overdue' => 'overdue', 'partially_paid' => 'partially_paid', 'unpaid' => 'unpaid',
            'empty_and_late' => 'unpaid', 'due_today' => 'unpaid',
        ];

        foreach ($cases as $case => $invoice) {
            $this->assertSame($expected[$case], $invoice->display_status, $case);

            $matches = Invoice::whereDisplayStatus($invoice->display_status)->pluck('id');
            $this->assertContains($invoice->id, $matches, $case);

            foreach (array_keys(Invoice::displayStatuses()) as $other) {
                if ($other !== $invoice->display_status) {
                    $this->assertNotContains($invoice->id, Invoice::whereDisplayStatus($other)->pluck('id'), "$case as $other");
                }
            }
        }
    }
}
