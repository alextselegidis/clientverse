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

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DeactivatedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_user_ends_their_active_session(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->update(['is_active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_session_guard_drops_a_deactivated_user(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        // Both the session and the "remember me" recaller resolve the user through
        // the guard, so dropping it here covers either way in.
        $guard = Auth::guard('web');
        $guard->setUser($user);

        $this->assertNull($guard->user());
        $this->assertFalse($guard->check());
    }

    public function test_deactivated_user_cannot_use_their_api_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')->assertOk();

        $user->update(['is_active' => false]);

        // Test requests share one container, so drop the guard's cached user.
        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }
}
