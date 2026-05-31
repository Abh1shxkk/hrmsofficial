<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLES = [
        'super_admin',
        'hr_admin',
        'manager',
        'employee',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : explode('|', $roles);

        return in_array($this->role, $roles, true);
    }

    public function assignRole(string $role): void
    {
        if (! in_array($role, self::ROLES, true)) {
            throw new \InvalidArgumentException("Invalid role [{$role}].");
        }

        $this->forceFill(['role' => $role])->save();
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
