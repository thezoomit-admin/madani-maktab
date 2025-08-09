<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory; 
    protected $fillable = [
    'user_id',
    'method',
    'route',
    'action',
    'ip_address',
    'user_agent',
    'browser_url',
    'accessed_at',
    'hostname',
    'platform',
    'uptime',
    'request_status_code',
    'response_status_code',
    'timestamp',
];


    protected $casts = [
        'accessed_at' => 'datetime',
        'timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
