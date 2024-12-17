<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRegister extends Model
{
    use HasFactory; 
    protected $fillable = [
        'user_id', 
        'reg_id', 
        'handwriting_images',
        'name',
        'father_name',
        'department_id',
        'bangla_study_status',
        'bangla_others_study',
        'arabi_study_status',
        'arabi_others_study',
        'study_info_after_seven', 
        'previous_institution',
        'hifz_para',
        'is_other_kitab_study',
        'kitab_jamat',
        'is_bangla_handwriting_clear',
        'kitab_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     
}
