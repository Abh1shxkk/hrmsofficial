<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id', 'basic', 'hra', 'transport_allowance',
        'other_allowances', 'effective_from', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'hra' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'effective_from' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
