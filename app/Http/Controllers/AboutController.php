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

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AboutController extends Controller
{
    private const FEED_URL = 'https://clientverse.org/rss.xml';

    private const CACHE_KEY = 'about.blog_posts';

    private const MAX_POSTS = 5;

    public function index()
    {
        return view('pages.about', ['posts' => $this->latestPosts()]);
    }

    /**
     * The blog lives on clientverse.org, so the About page reads its feed - the
     * only outgoing request the application makes.
     *
     * An install with no internet access would otherwise wait on the timeout on
     * every visit, so an empty result is cached too, just for an hour rather than
     * a day. The section renders nothing when the feed cannot be read.
     */
    private function latestPosts(): array
    {
        $posts = Cache::get(self::CACHE_KEY);

        if ($posts === null) {
            $posts = $this->fetchPosts();

            Cache::put(self::CACHE_KEY, $posts, $posts === [] ? now()->addHour() : now()->addDay());
        }

        return $posts;
    }

    private function fetchPosts(): array
    {
        $body = rescue(fn () => Http::connectTimeout(3)->timeout(5)->get(self::FEED_URL)->body(), null);

        $feed = $body === null ? null : rescue(fn () => new \SimpleXMLElement($body), null);

        if ($feed === null) {
            return [];
        }

        $posts = [];

        foreach ($feed->channel->item ?? [] as $item) {
            $link = trim((string) $item->link);

            // The feed is remote content, so only plain https links are followed.
            if (! filter_var($link, FILTER_VALIDATE_URL) || ! str_starts_with($link, 'https://')) {
                continue;
            }

            $timestamp = strtotime((string) $item->pubDate);

            $posts[] = [
                'title' => trim((string) $item->title),
                'link' => $link,
                'date' => $timestamp === false ? null : Carbon::createFromTimestampUTC($timestamp),
            ];
        }

        // The feed is not published in date order.
        usort($posts, fn (array $a, array $b) => ($b['date']?->timestamp ?? 0) <=> ($a['date']?->timestamp ?? 0));

        return array_slice($posts, 0, self::MAX_POSTS);
    }
}
