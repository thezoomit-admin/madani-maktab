<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentNote extends Model
{
    use HasFactory; 
    protected $fillable = [
        'employee_id',
        'student_id',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
 
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
