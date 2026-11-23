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

use App\Models\Concerns\HasCode;
use App\Enums\ProjectStatusEnum;
use App\Models\Concerns\HasCustomFields;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasCode;
    use HasCustomFields;
    use RecordsActivity;
    use SoftDeletes;

    public const CODE_PREFIX = 'P';

    protected $fillable = [
        'customer_id',
        'name',
        'description',
        'start_date',
        'due_date',
        'status',
        'visibility',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members');
    }

    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public static function statuses(): array
    {
        return ProjectStatusEnum::labels();
    }

    public static function visibilities(): array
    {
        return [
            'internal' => __('internal_only'),
            'shared' => __('shared_with_customer'),
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
