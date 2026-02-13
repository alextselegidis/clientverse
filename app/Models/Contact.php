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

class Contact extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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

        return $query->selectRaw('name AS label, id AS value')->get();
    }
}
