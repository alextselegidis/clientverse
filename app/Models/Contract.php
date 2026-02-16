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

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'project_id',
        'sale_id',
        'title',
        'description',
        'value',
        'currency',
        'type',
        'status',
        'start_date',
        'end_date',
        'signed_date',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public static function types(): array
    {
        return [
            'fixed' => __('fixed'),
            'recurring' => __('recurring'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'draft' => __('draft'),
            'active' => __('active'),
            'expired' => __('expired'),
            'cancelled' => __('cancelled'),
        ];
    }

    public static function toOptions($where = null)
    {
        $query = self::query();

        if ($where) {
            $query->where($where);
        }

        return $query->selectRaw('title AS label, id AS value')->get();
    }
}
