<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'is_enabled' => false,
            'mailer' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'from_name' => config('app.name', 'HRMS'),
        ]);
    }
}
