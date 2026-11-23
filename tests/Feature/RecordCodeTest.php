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
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_code_shows_on_the_record_and_search_opens_it(): void
    {
        $customer = Customer::create(['name' => 'Acme']);
        $code = 'C' . str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT);
        $admin = User::factory()->create();

        $this->assertSame($code, $customer->code);
        $this->actingAs($admin)->get(route('customers.show', $customer->id))->assertSee($code);
        $this->actingAs($admin)->get(route('search', ['q' => strtolower($code)]))
            ->assertRedirect(route('customers.show', $customer->id));

        // No permission for the module, or no such record: the normal search page.
        $this->actingAs(User::factory()->create(['role' => 'user', 'permissions' => ['tasks.view']]))
            ->get(route('search', ['q' => $code]))->assertOk();
        $this->actingAs($admin)->get(route('search', ['q' => 'C9999']))->assertOk();
        $this->assertSame('T0007', (new Task(['title' => 'x']))->forceFill(['id' => 7])->code);
    }
}
