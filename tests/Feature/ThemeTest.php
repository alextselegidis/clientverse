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
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_chosen_theme_is_kept_and_rendered(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->put(route('account.theme'), ['theme' => 'dark'])->assertRedirect();
        $this->put(route('account.theme'), ['theme' => 'neon'])->assertSessionHasErrors('theme');

        $this->assertSame('dark', $user->fresh()->theme);
        $this->get(route('dashboard'))->assertSee('var theme = "dark"', false);
    }
}
