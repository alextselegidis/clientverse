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

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

/**
 * ponytail: questions live in a JSON column on the survey rather than their own
 * table. Editing a survey that already has responses can shift question indexes;
 * give questions their own table with stable ids if that starts to matter.
 */
class Survey extends Model
{
    use RecordsActivity;
    use SoftDeletes;

    public const TYPES = ['text', 'choice', 'rating'];

    protected $fillable = ['title', 'description', 'is_open', 'questions'];

    protected $casts = [
        'is_open' => 'boolean',
        'questions' => 'array',
    ];

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class)->latest();
    }

    public function publicUrl(): string
    {
        return URL::signedRoute('public.surveys.show', ['survey' => $this->id]);
    }

    /**
     * Per question: option counts for choices, the average for ratings, and the
     * written answers for text.
     */
    public function results(): array
    {
        $answers = $this->responses->pluck('answers');

        return collect($this->questions)->map(function (array $question, int $index) use ($answers) {
            $given = $answers->map(fn ($set) => $set[$index] ?? null)->filter(fn ($answer) => filled($answer));

            return $question + match ($question['type']) {
                'choice' => ['counts' => collect($question['options'])->mapWithKeys(fn ($option) => [$option => $given->filter(fn ($answer) => $answer === $option)->count()])->all()],
                'rating' => (function () use ($given) {
                    $numbers = $given->filter(fn ($answer) => is_numeric($answer));

                    return ['average' => $numbers->isEmpty() ? null : round($numbers->avg(), 1), 'count' => $numbers->count()];
                })(),
                default => ['texts' => $given->values()->all()],
            };
        })->all();
    }
}
