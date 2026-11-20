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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_time_counts_closed_entries_and_a_running_timer(): void
    {
        Carbon::setTestNow('2026-11-20 12:00:00');

        $task = Task::create(['title' => 'Write report']);
        $task->timeEntries()->create(['started_at' => '2026-11-20 09:00', 'ended_at' => '2026-11-20 10:30']);
        $task->timeEntries()->create(['started_at' => '2026-11-20 11:15']);

        $this->assertSame(135, $task->minutes_logged);
        $this->assertSame('2h 15m', format_minutes($task->minutes_logged));
        $this->assertSame(1, $task->timeEntries()->running()->count());
    }

    public function test_only_open_tasks_past_their_due_date_are_overdue(): void
    {
        $this->assertTrue((new Task(['status' => 'todo', 'due_date' => today()->subDay()]))->is_overdue);
        $this->assertFalse((new Task(['status' => 'done', 'due_date' => today()->subDay()]))->is_overdue);
        $this->assertFalse((new Task(['status' => 'todo', 'due_date' => today()]))->is_overdue);
    }
}
