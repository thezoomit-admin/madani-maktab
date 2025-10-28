<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;  
    protected $fillable = [
        'attendance_id',
        'user_id',
        'in_time',
        'out_time',
        'device_id',
        'in_access_id',
        'out_access_id',
        'latitude',
        'longitude',
        'comment',
    ];

    /**
     * Get the attendance that owns the log.
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
    /**
     * Get the user that owns the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
