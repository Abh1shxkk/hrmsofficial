<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    public const TYPES = [
        'aadhar' => 'Aadhar',
        'pan' => 'PAN',
        'offer_letter' => 'Offer Letter',
        'contract' => 'Contract',
        'education' => 'Education Certificate',
        'experience' => 'Experience Letter',
        'other' => 'Other',
    ];

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }
}
