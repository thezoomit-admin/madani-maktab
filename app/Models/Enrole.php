<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrole extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'student_id',
        'department_id',
        'session',
        'year',
        'marks',
        'fee_type',
        'fee',
        'status',
        'is_yeada',
    ];
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
