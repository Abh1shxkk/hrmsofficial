<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends Model
{
    protected $fillable = [
        'employee_id', 'month', 'year', 'basic', 'hra', 'transport_allowance', 'other_allowances', 'gross_salary',
        'pf_employee', 'pf_employer', 'esi_employee', 'esi_employer',
        'tds', 'total_deductions', 'net_salary', 'working_days',
        'present_days', 'status', 'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'hra' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'pf_employee' => 'decimal:2',
            'pf_employer' => 'decimal:2',
            'esi_employee' => 'decimal:2',
            'esi_employer' => 'decimal:2',
            'tds' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'present_days' => 'decimal:1',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('month', now()->month)->where('year', now()->year);
    }
}
