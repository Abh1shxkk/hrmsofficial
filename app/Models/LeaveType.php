<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['name', 'code', 'max_days_per_year', 'is_carry_forward'];

    protected function casts(): array
    {
        return [
            'is_carry_forward' => 'boolean',
        ];
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }
}
