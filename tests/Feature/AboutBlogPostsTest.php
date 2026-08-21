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
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AboutBlogPostsTest extends TestCase
{
    use RefreshDatabase;

    private const FEED_URL = 'https://clientverse.org/rss.xml';

    public function test_about_page_lists_the_newest_posts_first(): void
    {
        // The real feed is not published in date order, and a javascript: link is
        // exactly what a compromised feed would carry.
        Http::fake([self::FEED_URL => Http::response($this->feed([
            ['Older post', 'https://clientverse.org/blog/older/', 'Tue, 14 Apr 2026 22:00:00 GMT'],
            ['Newest post', 'https://clientverse.org/blog/newest/', 'Thu, 06 Aug 2026 22:00:00 GMT'],
            ['Hostile post', 'javascript:alert(1)', 'Thu, 07 Aug 2026 22:00:00 GMT'],
        ]))]);

        $response = $this->actingAs(User::factory()->create())->get(route('about'));

        $response->assertOk()
            ->assertSeeInOrder(['Latest Blog Posts', 'Newest post', 'Aug 06, 2026', 'Older post', 'Apr 14, 2026'])
            ->assertDontSee('Hostile post')
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_the_feed_is_read_once_and_then_served_from_the_cache(): void
    {
        Http::fake([self::FEED_URL => Http::response($this->feed([
            ['Cached post', 'https://clientverse.org/blog/cached/', 'Thu, 06 Aug 2026 22:00:00 GMT'],
        ]))]);

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('about'))->assertSee('Cached post');
        $this->actingAs($user)->get(route('about'))->assertSee('Cached post');

        Http::assertSentCount(1);
    }

    public function test_an_unreachable_feed_leaves_the_rest_of_the_page_intact(): void
    {
        Http::fake([self::FEED_URL => Http::response('', 500)]);

        $response = $this->actingAs(User::factory()->create())->get(route('about'));

        $response->assertOk()
            ->assertSee('Go Premium')
            ->assertDontSee('Latest Blog Posts');
    }

    private function feed(array $items): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Clientverse</title>';

        foreach ($items as [$title, $link, $pubDate]) {
            $xml .= "<item><title>$title</title><link>$link</link><pubDate>$pubDate</pubDate></item>";
        }

        return $xml.'</channel></rss>';
    }
}
