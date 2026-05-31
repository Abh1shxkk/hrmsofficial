<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id', 'department_id', 'employee_code', 'designation',
        'date_of_birth', 'date_of_joining', 'phone', 'address',
        'photo', 'aadhar_number', 'pan_number', 'employment_type', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function salarySlips(): HasMany
    {
        return $this->hasMany(SalarySlip::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
