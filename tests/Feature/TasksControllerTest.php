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
use Illuminate\Support\Carbon;
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

    public function test_one_timer_runs_per_person_and_entries_stay_on_their_task(): void
    {
        Carbon::setTestNow('2026-11-21 12:00:00');

        $user = User::factory()->create();
        $first = Task::create(['title' => 'First']);
        $second = Task::create(['title' => 'Second']);

        $this->actingAs($user)->post(route('tasks.timer.start', $first));
        $this->actingAs($user)->post(route('tasks.timer.start', $second));

        $this->assertSame(0, $first->timeEntries()->running()->count());
        $this->assertSame(1, $second->timeEntries()->running()->count());
        $this->actingAs($user)->get(route('tasks.show', $second))->assertOk()->assertSee(__('stop_timer'));

        $this->actingAs($user)->post(route('tasks.timer.stop', $second));
        $this->assertSame(0, $second->timeEntries()->running()->count());

        $this->actingAs($user)
            ->post(route('tasks.time-entries.store', $first), ['started_at' => '2026-11-20T09:00', 'ended_at' => '2026-11-20T08:00'])
            ->assertSessionHasErrors('ended_at');

        $this->actingAs($user)
            ->post(route('tasks.time-entries.store', $first), ['started_at' => '2026-11-20T09:00', 'ended_at' => '2026-11-20T11:00'])
            ->assertSessionHasNoErrors();

        $entry = $first->timeEntries()->whereDate('started_at', '2026-11-20')->sole();
        $this->assertSame(120, $entry->minutes);

        $this->actingAs($user)->delete(route('tasks.time-entries.destroy', [$second, $entry]))->assertNotFound();
        $this->actingAs($user)->delete(route('tasks.time-entries.destroy', [$first, $entry]));
        $this->assertNull($entry->fresh());
    }
}
