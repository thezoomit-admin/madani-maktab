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
        'handwriting_image',
        'name',
        'father_name',
        'department_id',
        'bangla_study_status',
        'bangla_others_study',
        'arabi_study_status',
        'arabi_others_study', 
        'previous_education_details',
        'hifz_para',
        'is_other_kitab_study',
        'kitab_jamat',
        'is_bangla_handwriting_clear', 
        'note',
        'is_existing',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    } 

    public static function nextMaktabId(){
        $largest_student_id = StudentRegister::where('reg_id', 'like', 'ম-%')
        ->pluck('reg_id')
        ->map(function ($id) {
            return preg_replace("/[^0-9]/", "", $id);
        })
        ->max();
    
        $largest_student_id = $largest_student_id ? $largest_student_id : 1000;  
        $largest_student_id++;
        $new_student_id = 'ম-' . $largest_student_id; 
        return $new_student_id; 
    } 
     
    public static function nextKitabId(){
        $largest_student_id = StudentRegister::where('reg_id', 'like', 'ক-%')
        ->pluck('reg_id')
        ->map(function ($id) {
            return preg_replace("/[^0-9]/", "", $id);
        })
        ->max(); 
        $largest_student_id = $largest_student_id ? $largest_student_id : 5000;  
        $largest_student_id++;
        $new_student_id = 'ক-' . $largest_student_id; 
        return $new_student_id; 
    }  

     
}
