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
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    /**
     * The fields a signature covers; they cannot change once the contract is signed.
     */
    public const SIGNED_TERMS = ['customer_id', 'title', 'description', 'value', 'currency', 'start_date', 'end_date'];

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
        'signed_at' => 'datetime',
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

    /**
     * A permanent link the customer can open without an account to read and sign.
     */
    public function signingUrl(): string
    {
        return URL::signedRoute('public.contracts.show', ['contract' => $this->id]);
    }

    /**
     * Record an electronic signature: who typed their name, from where, and when.
     */
    public function canBeSigned(): bool
    {
        return !$this->signed_at && in_array($this->status, ['draft', 'active'], true);
    }

    public function sign(string $name, ?string $ip): void
    {
        $this->signer_name = $name;
        $this->signer_ip = $ip;
        $this->signed_at = now();
        $this->signed_date = today();

        if ($this->status === 'draft') {
            $this->status = 'active';
        }

        $this->save();
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
