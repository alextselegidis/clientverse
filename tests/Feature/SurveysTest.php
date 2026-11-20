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

use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveysTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_survey_is_built_answered_through_its_link_and_summarised(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('surveys.create'))->assertOk();
        $this->actingAs($user)
            ->post(route('surveys.store'), [
                'title' => 'Support feedback',
                'is_open' => '1',
                'questions' => [
                    ['text' => 'How did we do?', 'type' => 'rating', 'options' => ''],
                    ['text' => 'Which channel?', 'type' => 'choice', 'options' => "Email\nPhone\n"],
                    ['text' => 'Anything else?', 'type' => 'text', 'options' => ''],
                    ['text' => '', 'type' => 'text', 'options' => ''],
                ],
            ])
            ->assertSessionHasNoErrors();

        $survey = Survey::sole();
        $this->assertCount(3, $survey->questions);
        $this->assertSame(['Email', 'Phone'], $survey->questions[1]['options']);

        auth()->logout();
        $url = $survey->publicUrl();

        $this->get(route('public.surveys.show', $survey))->assertForbidden();
        $this->get($url)->assertOk()->assertSee('Which channel?');
        $this->from($url)->post($url, ['answers' => [5, 'Email', 'Great']])->assertRedirect($url);
        $this->from($url)->post($url, ['answers' => [3, 'Email']]);
        $this->from($url)->post($url, ['answers' => [9, 'Fax']])->assertSessionHasErrors(['answers.0', 'answers.1']);

        $results = $survey->fresh()->results();
        $this->assertSame(4.0, $results[0]['average']);
        $this->assertSame(['Email' => 2, 'Phone' => 0], $results[1]['counts']);
        $this->assertSame(['Great'], $results[2]['texts']);

        $this->actingAs($user)->get(route('surveys.show', $survey))->assertOk()->assertSee('Great');

        $survey->update(['is_open' => false]);
        auth()->logout();
        $this->post($url, ['answers' => [1]])->assertNotFound();
    }
}
