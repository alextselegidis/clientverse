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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * An extra field an admin adds to one kind of record. Records keep their
 * answers in a custom_values JSON column, keyed by field id (HasCustomFields).
 */
class CustomField extends Model
{
    protected $fillable = ['applies_to', 'label', 'type', 'options', 'is_required', 'position'];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * The fields of one kind of record, in form order.
     */
    public static function for(string $appliesTo): Collection
    {
        return self::where('applies_to', $appliesTo)->orderBy('position')->orderBy('id')->get();
    }

    /**
     * Validation rules for the custom_values[...] inputs of a record form,
     * to merge into the form's own rules.
     */
    public static function rules(string $appliesTo): array
    {
        $rules = ['custom_values' => 'nullable|array'];

        foreach (self::for($appliesTo) as $field) {
            $rules['custom_values.' . $field->id] = array_merge(
                [$field->is_required && $field->type !== 'checkbox' ? 'required' : 'nullable'],
                match ($field->type) {
                    'number' => ['numeric', 'between:-999999999999,999999999999'],
                    'date' => ['date'],
                    'select' => [Rule::in($field->options ?? [])],
                    'checkbox' => ['boolean'],
                    'textarea' => ['string', 'max:5000'],
                    default => ['string', 'max:255'],
                },
            );
        }

        return $rules;
    }

    /**
     * Field labels for the validation messages, matching rules().
     */
    public static function attributes(string $appliesTo): array
    {
        return self::for($appliesTo)
            ->mapWithKeys(fn (self $field) => ['custom_values.' . $field->id => $field->label])
            ->all();
    }

    /**
     * Turn a submitted value into what gets stored.
     */
    public function normalize(mixed $value): mixed
    {
        if ($this->type === 'checkbox') {
            return (bool) $value;
        }

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * A stored value as it reads on a detail page.
     */
    public function format(mixed $value): string
    {
        return match (true) {
            $this->type === 'checkbox' => $value ? __('yes') : __('no'),
            blank($value) => '',
            $this->type === 'date' => \Illuminate\Support\Carbon::parse($value)->format('M d, Y'),
            default => (string) $value,
        };
    }

    public static function appliesToOptions(): array
    {
        return [
            'customers' => __('customers'),
            'contacts' => __('contacts'),
            'projects' => __('projects'),
            'sales' => __('sales'),
            'contracts' => __('contracts'),
        ];
    }

    public static function types(): array
    {
        return [
            'text' => __('text'),
            'textarea' => __('long_text'),
            'number' => __('number'),
            'date' => __('date'),
            'select' => __('dropdown'),
            'checkbox' => __('checkbox'),
        ];
    }
}
