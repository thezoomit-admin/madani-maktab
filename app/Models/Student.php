<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'student_id',
        'name',
        'father_name',
        'department_id',
        'bangla_study_status',
        'bangla_others_study',
        'arabi_study_status',
        'arabi_others_study',
        'study_info_after_seven',
        'handwriting_image',
        'profile_image',
        'previous_institution',
        'hifz_para',
        'is_other_kitab_study',
        'kitab_jamat',
        'is_bangla_handwriting_clear',
        'kitab_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}
