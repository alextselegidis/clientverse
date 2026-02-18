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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Contact extends Model
{
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'role',
        'is_primary',
        'has_portal_access',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'has_portal_access' => 'boolean',
    ];

    protected $appends = ['full_name'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the contact's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . ($this->last_name ?? '')),
        );
    }

    public static function roles(): array
    {
        return [
            'decision_maker' => __('decision_maker'),
            'finance' => __('finance'),
            'technical' => __('technical'),
            'other' => __('other'),
        ];
    }

    public static function toOptions($where = null)
    {
        $query = self::query();

        if ($where) {
            $query->where($where);
        }

        return $query->selectRaw("CONCAT(first_name, ' ', COALESCE(last_name, '')) AS label, id AS value")->get();
    }
}
