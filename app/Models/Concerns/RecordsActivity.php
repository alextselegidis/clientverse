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

namespace App\Models\Concerns;

use App\Models\Activity;
use Illuminate\Support\Str;

/**
 * Writes an Activity row whenever the model is created, updated or deleted.
 * An update that only touches bookkeeping columns (timestamps, cached totals)
 * is not worth a line in the log, so it is skipped.
 */
trait RecordsActivity
{
    private static array $quietAttributes = [
        'created_at', 'updated_at', 'deleted_at', 'remember_token', 'total', 'amount_paid', 'reminded_at',
    ];

    public static function bootRecordsActivity(): void
    {
        static::created(fn (self $model) => $model->recordActivity('created'));
        static::updated(fn (self $model) => $model->recordActivity('updated'));
        static::deleted(fn (self $model) => $model->recordActivity('deleted'));
    }

    protected function recordActivity(string $event): void
    {
        $changes = null;

        if ($event === 'updated') {
            $skip = array_merge(self::$quietAttributes, $this->getHidden());
            $changed = array_diff(array_keys($this->getChanges()), $skip);

            if (!$changed) {
                return;
            }

            // Old and new values side by side, trimmed so a long body cannot bloat the log.
            foreach ($changed as $key) {
                $changes[$key] = [
                    Str::limit((string) $this->getRawOriginal($key), 100),
                    Str::limit((string) ($this->getAttributes()[$key] ?? ''), 100),
                ];
            }
        }

        Activity::create([
            'user_id' => auth('web')->id(),
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'event' => $event,
            'label' => Str::limit($this->activityLabel(), 250),
            'changes' => $changes,
        ]);
    }

    /**
     * What the record is called in the log.
     */
    public function activityLabel(): string
    {
        $name = trim((string) ($this->name ?? $this->title ?? $this->subject ?? $this->number ?? $this->description
            ?? $this->first_name . ' ' . $this->last_name));

        return $name !== '' ? $name : '#' . $this->getKey();
    }
}
