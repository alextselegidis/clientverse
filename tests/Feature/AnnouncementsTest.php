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

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_current_announcements_for_the_audience_are_shown(): void
    {
        Announcement::create(['title' => 'Staff only', 'body' => 'x', 'audience' => 'staff']);
        Announcement::create(['title' => 'For all', 'body' => 'x', 'audience' => 'everyone', 'visible_until' => today()]);
        Announcement::create(['title' => 'Customers', 'body' => 'x', 'audience' => 'customers']);
        Announcement::create(['title' => 'Expired', 'body' => 'x', 'audience' => 'everyone', 'visible_until' => today()->subDay()]);

        $this->assertEqualsCanonicalizing(['Staff only', 'For all'], Announcement::shownTo('staff')->pluck('title')->all());
        $this->assertEqualsCanonicalizing(['Customers', 'For all'], Announcement::shownTo('customers')->pluck('title')->all());

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('dashboard'))
            ->assertSee('Staff only')
            ->assertDontSee('Expired');
    }

    public function test_only_admins_manage_announcements_and_the_body_is_escaped(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->post(route('setup.announcements.store'), ['title' => 'T', 'body' => 'B', 'audience' => 'staff'])
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('setup.announcements.store'), ['title' => 'Hi', 'body' => '<script>x</script>', 'audience' => 'staff'])
            ->assertRedirect(route('setup.announcements'));

        $this->get(route('dashboard'))->assertDontSee('<script>x</script>', false);
    }
}
