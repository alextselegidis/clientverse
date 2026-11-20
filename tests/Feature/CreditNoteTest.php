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

use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_issued_credit_reduces_the_balance_of_its_invoice_and_releases_it_when_moved(): void
    {
        $invoice = Invoice::create(['status' => 'sent', 'currency' => 'EUR']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 500]]);

        $credit = CreditNote::create(['status' => 'draft', 'currency' => 'EUR']);
        $credit->syncItems([['description' => 'Goodwill', 'quantity' => 1, 'unit_price' => 100]]);
        $credit->invoice()->associate($invoice)->save();

        $this->assertEquals(500.00, $invoice->fresh()->balance, 'a draft credit does not count');

        $credit->update(['status' => 'issued']);
        $this->assertEquals(400.00, $invoice->fresh()->balance);
        $this->assertEquals(100.00, $invoice->fresh()->credited);
        $this->assertSame('partially_paid', $invoice->fresh()->display_status);

        $credit->syncItems([['description' => 'Goodwill', 'quantity' => 1, 'unit_price' => 500]]);
        $this->assertSame('paid', $invoice->fresh()->display_status);

        $credit->invoice()->dissociate()->save();
        $this->assertEquals(500.00, $invoice->fresh()->balance);

        $credit->invoice()->associate($invoice)->save();
        $credit->delete();
        $this->assertEquals(500.00, $invoice->fresh()->balance);
        $this->assertStringStartsWith('CN-', $credit->number);
    }
}
