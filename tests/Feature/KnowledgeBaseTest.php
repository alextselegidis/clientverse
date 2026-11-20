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

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_public_articles_reach_the_public_knowledge_base(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('articles.store'), [
                'title' => 'Reset your password',
                'category' => 'Accounts',
                'body' => "Click **Forgot password**.\n\n<script>alert(1)</script>\n\n[bad](javascript:alert(1))",
                'is_public' => '1',
            ])
            ->assertSessionHasNoErrors();
        Article::create(['title' => 'Internal runbook', 'body' => 'Secret', 'is_public' => false]);

        $article = Article::where('slug', 'reset-your-password')->sole();
        $this->actingAs($user)->get(route('articles'))->assertOk()->assertSee('Internal runbook');
        $this->actingAs($user)->get(route('articles.show', $article))->assertOk();

        auth()->logout();

        $this->get(route('kb'))->assertOk()->assertSee('Reset your password')->assertDontSee('Internal runbook');
        $this->get(route('kb.show', 'reset-your-password'))
            ->assertOk()
            ->assertSee('<strong>Forgot password</strong>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert', false);
        $this->get(route('kb.show', 'internal-runbook'))->assertNotFound();
    }

    public function test_slugs_stay_unique_and_fixed(): void
    {
        $first = Article::create(['title' => 'FAQ', 'body' => 'a']);
        $second = Article::create(['title' => 'FAQ', 'body' => 'b']);
        $first->update(['title' => 'Frequently asked']);

        $this->assertSame('faq', $first->fresh()->slug);
        $this->assertSame('faq-2', $second->slug);
    }
}
