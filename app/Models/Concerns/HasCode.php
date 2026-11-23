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
 * A short code people can share, such as C0042: the model's CODE_PREFIX and its id padded to four digits.
 * Derived from the id, so it is unique, never changes and needs no column; id 10000 reads C10000.
 */
trait HasCode
{
    public function getCodeAttribute(): string
    {
        return static::CODE_PREFIX . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}
