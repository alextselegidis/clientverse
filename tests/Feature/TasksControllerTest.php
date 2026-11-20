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

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TasksControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_task_is_created_and_listed_for_its_assignee(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('tasks.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'title' => 'Renew SSL certificate',
                'status' => 'todo',
                'priority' => 'high',
                'assigned_to' => $user->id,
            ])
            ->assertSessionHasNoErrors();

        Task::create(['title' => 'Someone else', 'assigned_to' => $other->id]);
        $task = Task::where('title', 'Renew SSL certificate')->sole();

        $this->actingAs($user)->get(route('tasks', ['mine' => 1]))
            ->assertOk()->assertSee('Renew SSL certificate')->assertDontSee('Someone else');
        $this->actingAs($user)->get(route('tasks.show', $task))->assertOk();
        $this->actingAs($user)->get(route('tasks.edit', $task))->assertOk();

        $this->actingAs($user)
            ->post(route('tasks.store'), ['title' => 'Bad', 'status' => 'nope', 'priority' => 'normal'])
            ->assertSessionHasErrors('status');
    }
}
