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
use App\Models\Estimate;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimateTest extends TestCase
{
    use RefreshDatabase;

    public function test_converting_copies_the_lines_once_and_accepts_the_estimate(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        $estimate = Estimate::create(['customer_id' => $customer->id, 'currency' => 'EUR', 'status' => 'sent']);
        $estimate->syncItems([['description' => 'Audit', 'quantity' => 2, 'unit_price' => 300, 'tax_rate' => 24]]);

        $invoice = $estimate->convertToInvoice();

        $this->assertEquals(744.00, $estimate->total);
        $this->assertEquals(744.00, $invoice->total);
        $this->assertSame('Audit', $invoice->items()->sole()->description);
        $this->assertSame('accepted', $estimate->fresh()->status);
        $this->assertTrue($estimate->fresh()->convertToInvoice()->is($invoice));
        $this->assertSame(1, Invoice::count());
        $this->assertStringStartsWith('EST-', $estimate->number);
    }
}
