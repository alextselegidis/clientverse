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
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
        'company',
        'address',
        'billing_address',
        'vat_id',
        'currency',
        'type',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'customer_tag');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function customerNotes()
    {
        return $this->hasMany(CustomerNote::class)->orderBy('created_at', 'desc');
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class)->where('is_primary', true);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company) {
            return $this->name . ' @ ' . $this->company;
        }
        return $this->name;
    }

    public static function types(): array
    {
        return [
            'company' => __('company'),
            'individual' => __('individual'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'lead' => __('lead'),
            'active' => __('active'),
            'inactive' => __('inactive'),
        ];
    }

    public static function toOptions($where = null)
    {
        $query = self::query();

        if ($where) {
            $query->where($where);
        }

        return $query
            ->selectRaw(
                "
                CONCAT(name,
                    IF(company IS NOT NULL AND company != '', CONCAT(' @ ', company), '')
                ) AS label,
                id AS value
            ",
            )
            ->get();
    }
}
