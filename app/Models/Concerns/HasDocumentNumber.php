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

/**
 * Gives a document a readable number such as INV-000042 when none was typed in.
 * The using model sets NUMBER_PREFIX.
 */
trait HasDocumentNumber
{
    protected static function bootHasDocumentNumber(): void
    {
        // The id is only known after the insert, so a blank number is filled in afterwards.
        static::created(function (self $document) {
            if (filled($document->number)) {
                return;
            }

            $base = sprintf('%s-%06d', static::NUMBER_PREFIX, $document->id);
            $number = $base;

            // Someone may already have typed this number by hand on another document.
            for ($i = 2; static::withTrashed()->where('number', $number)->exists(); $i++) {
                $number = $base . '-' . $i;
            }

            $document->number = $number;
            $document->saveQuietly();
        });
    }
}
