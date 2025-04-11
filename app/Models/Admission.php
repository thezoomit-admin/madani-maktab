<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    use HasFactory; 
    protected $fillable = [
        'user_id',
        'reg_id',
        'name',
        'father_name',
        'department_id',
        'interested_session',
        'last_year_session',
        'last_year_id',
        'original_id',
        'total_marks',
        'average_marks',
        'status',
        'student_id',
    ];
    
}
