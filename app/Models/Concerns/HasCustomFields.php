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

use App\Models\CustomField;
use Illuminate\Support\Collection;

/**
 * Lets a record carry the values of the CustomFields defined for its table.
 * Only fields that exist are kept, so a forged custom_values[999] is dropped.
 */
trait HasCustomFields
{
    public function initializeHasCustomFields(): void
    {
        $this->mergeFillable(['custom_values']);
        $this->mergeCasts(['custom_values' => 'array']);
    }

    /**
     * Submitted values replace the stored ones field by field, so an API call
     * that sends one field leaves the others as they were.
     */
    public function setCustomValuesAttribute(?array $values): void
    {
        $values ??= [];
        $stored = [];

        foreach (CustomField::for($this->getTable()) as $field) {
            $stored[$field->id] = array_key_exists($field->id, $values)
                ? $field->normalize($values[$field->id])
                : $this->custom_values[$field->id] ?? null;
        }

        // An object, so field ids stay keys even if they happen to run 0, 1, 2...
        $stored = array_filter($stored, fn ($value) => $value !== null);
        $this->attributes['custom_values'] = json_encode((object) $stored);
    }

    /**
     * [field, value] pairs for the detail page, in form order.
     */
    public function customFieldValues(): Collection
    {
        return CustomField::for($this->getTable())
            ->map(fn (CustomField $field) => [$field, $this->custom_values[$field->id] ?? null])
            ->toBase();
    }
}
