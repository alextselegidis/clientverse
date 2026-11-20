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

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'is_active', 'role', 'permissions'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::ADMIN->value;
    }

    /**
     * The modules a permission can be granted on, and the actions per module.
     * Creating counts as editing.
     */
    public const PERMISSION_MODULES = [
        'customers', 'projects', 'tasks', 'sales', 'contracts', 'invoices', 'estimates', 'items', 'tickets',
    ];

    public const PERMISSION_ACTIONS = ['view', 'edit', 'delete'];

    /**
     * Admins and users without a permission list may do everything; everyone
     * else needs "module.action" in their list.
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        if ($this->isAdmin() || $this->permissions === null) {
            return true;
        }

        return in_array("{$module}.{$action}", $this->permissions, true);
    }
}
