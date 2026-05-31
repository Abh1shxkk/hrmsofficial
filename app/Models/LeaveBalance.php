<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'year', 'allocated', 'carried_forward', 'used', 'pending', 'balance',
    ];

    protected function casts(): array
    {
        return [
            'allocated' => 'decimal:2',
            'carried_forward' => 'decimal:2',
            'used' => 'decimal:2',
            'pending' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($balance) {
            $balance->balance = $balance->allocated + ($balance->carried_forward ?? 0) - $balance->used - $balance->pending;
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
