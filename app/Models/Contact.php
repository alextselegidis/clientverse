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

use App\Models\Concerns\HasCustomFields;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A person at a customer. Contacts with portal access and a password sign in to
 * the client portal through the "contact" guard. The password is set by staff
 * on purpose outside $fillable, so neither the edit form nor the API mass-assigns it.
 */
class Contact extends Authenticatable
{
    use HasCustomFields;
    use RecordsActivity;

    protected $hidden = ['password', 'remember_token'];

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
        'password' => 'hashed',
    ];

    protected $appends = ['full_name'];

    /**
     * Read by AppSessionGuard on every request, as it is for users: taking portal
     * access away signs the contact out.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->has_portal_access && filled($this->password);
    }

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
