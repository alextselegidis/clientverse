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

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_changes_are_logged_with_who_made_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Ada']);
        $this->actingAs($admin);

        $customer = Customer::create(['name' => 'Acme']);
        $customer->update(['name' => 'Acme Ltd']);
        $customer->touch();
        $customer->delete();

        $log = Activity::where('subject_type', Customer::class)->orderBy('id')->get();

        $this->assertSame(['created', 'updated', 'deleted'], $log->pluck('event')->all());
        $this->assertSame(['Acme', 'Acme Ltd'], $log[1]->changes['name']);
        $this->assertSame('Acme Ltd', $log[2]->label);
        $this->assertTrue($log->every(fn (Activity $activity) => $activity->user_id === $admin->id));

        $this->get(route('setup.activity'))->assertOk()->assertSee('Acme Ltd')->assertSee('Ada');
    }

    public function test_bookkeeping_saves_and_hidden_fields_stay_out_of_the_log(): void
    {
        $invoice = Invoice::create(['customer_id' => Customer::create(['name' => 'Acme'])->id, 'status' => 'draft']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 1, 'unit_price' => 100]]);
        $invoice->recalculate();

        $user = User::factory()->create();
        $user->update(['password' => 'something-new']);

        $this->assertSame(['created'], Activity::where('subject_type', Invoice::class)->pluck('event')->all());
        $this->assertFalse(Activity::where('subject_type', User::class)->where('event', 'updated')->exists());
        $this->assertNull(Activity::firstWhere('subject_type', Invoice::class)->user_id);
    }

    public function test_old_entries_are_pruned(): void
    {
        Customer::create(['name' => 'Acme']);
        Activity::query()->update(['created_at' => now()->subDays(Activity::KEEP_DAYS + 1)]);
        Customer::create(['name' => 'Fresh']);

        $this->artisan('model:prune', ['--model' => [Activity::class]]);

        $this->assertSame(['Fresh'], Activity::pluck('label')->all());
    }
}
