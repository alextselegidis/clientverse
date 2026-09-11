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
use App\Models\CustomerNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_password_change_needs_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password-1']);

        $this->actingAs($user)
            ->put(route('account.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password-1', $user->fresh()->password));
    }

    public function test_the_current_password_lets_the_change_through_and_drops_api_tokens(): void
    {
        $user = User::factory()->create(['password' => 'current-password-1']);
        $user->createToken('cli');

        $this->actingAs($user)
            ->put(route('account.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'current-password-1',
                'password' => 'new-password-1',
                'password_confirmation' => 'new-password-1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_an_email_change_needs_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'current-password-1']);

        $this->actingAs($user)
            ->put(route('account.update'), [
                'name' => $user->name,
                'email' => 'taken-over@example.org',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertNotSame('taken-over@example.org', $user->fresh()->email);
    }

    public function test_a_list_is_not_sorted_by_a_hidden_column_or_an_invalid_direction(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // A hidden column would leak its content one comparison at a time, and an
        // unknown direction used to reach Eloquent and throw.
        $this->actingAs($admin)->get(route('setup.users', ['sort' => 'password', 'direction' => 'asc']))->assertOk();
        $this->actingAs($admin)->get(route('setup.users', ['sort' => 'name', 'direction' => 'sideways']))->assertOk();
        $this->actingAs($admin)->get(route('setup.users', ['sort' => 'no_such_column', 'direction' => 'asc']))->assertOk();
    }

    public function test_a_note_cannot_be_reached_through_another_customer(): void
    {
        $user = User::factory()->create();
        $owner = Customer::create(['name' => 'Owning Customer']);
        $other = Customer::create(['name' => 'Other Customer']);

        $note = CustomerNote::create([
            'customer_id' => $owner->id,
            'user_id' => $user->id,
            'content' => 'Private note',
        ]);

        $this->actingAs($user)
            ->delete(route('customers.notes.destroy', ['customer' => $other->id, 'note' => $note->id]))
            ->assertNotFound();

        $this->assertDatabaseHas('customer_notes', ['id' => $note->id]);
    }
}
