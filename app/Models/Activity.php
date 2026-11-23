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

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One create, update or delete of a record, written by RecordsActivity.
 */
class Activity extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    /**
     * How long entries are kept before model:prune clears them.
     */
    public const KEEP_DAYS = 365;

    protected $fillable = ['user_id', 'subject_type', 'subject_id', 'event', 'label', 'changes'];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(self::KEEP_DAYS));
    }

    /**
     * "Invoice", "Credit note": the record type in words, from the class name.
     */
    public function getSubjectNameAttribute(): string
    {
        return __(Str::snake(class_basename($this->subject_type)));
    }

    /**
     * [class => label] for the record type filter, from the types logged so far.
     */
    public static function subjectTypes(): array
    {
        return self::query()->distinct()->orderBy('subject_type')->pluck('subject_type')
            ->mapWithKeys(fn ($type) => [$type => __(Str::snake(class_basename($type)))])
            ->all();
    }

    public static function eventColors(): array
    {
        return ['created' => 'success', 'updated' => 'info', 'deleted' => 'danger'];
    }
}
