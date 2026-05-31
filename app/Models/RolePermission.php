<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'module',
        'can_view',
        'can_edit',
        'can_delete',
        'can_manage',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
            'can_manage' => 'boolean',
        ];
    }
}
