<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'status', 'check_in', 'check_out', 'remarks', 'marked_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function markedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'wfh']);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }
}
