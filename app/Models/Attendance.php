<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;  
    protected $fillable = [
        'user_id',
        'reg_id',
        'in_time',
        'in_access_id',
        'out_time',
        'out_access_id',
        'comment',
        'comment_by',
    ];  
    
    /**
     * Get the user that owns the attendance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
