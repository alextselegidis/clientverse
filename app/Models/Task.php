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

namespace App\Models;

use App\Models\Concerns\HasCode;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasCode;
    use RecordsActivity;
    use SoftDeletes;

    public const CODE_PREFIX = 'T';

    protected $fillable = [
        'project_id',
        'customer_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class)->latest('started_at');
    }

    /**
     * Logged minutes, counting a running timer up to now.
     */
    public function getMinutesLoggedAttribute(): int
    {
        return $this->timeEntries->sum(fn (TimeEntry $entry) => $entry->minutes);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'done' && $this->due_date && $this->due_date->lt(today());
    }

    public static function statuses(): array
    {
        return ['todo' => __('todo'), 'in_progress' => __('in_progress'), 'done' => __('done')];
    }

    public static function statusColors(): array
    {
        return ['todo' => 'secondary', 'in_progress' => 'info', 'done' => 'success'];
    }

    public static function priorities(): array
    {
        return ['low' => __('low'), 'normal' => __('normal'), 'high' => __('high')];
    }
}
