<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'major_illness_history',
        'current_medication_details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    } 

    

    public static function nextMaktabId()
    {
        $startDate = Carbon::now()->subMonths(5); // last 5 months
        $endDate   = Carbon::now();

        $largest_student_id = StudentRegister::where('reg_id', 'like', 'ম-%')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('reg_id')
            ->map(function ($id) {
                return (int) preg_replace("/[^0-9]/", "", $id);
            })
            ->max();

        $largest_student_id = $largest_student_id ?? 0;
        $next_id = $largest_student_id + 1;

        $formatted_id = str_pad($next_id, 3, '0', STR_PAD_LEFT); 
        return 'ম-' . $formatted_id;
    } 
      
    public static function nextKitabId()
    {
        $startDate = Carbon::now()->subMonths(5); // last 5 months
        $endDate   = Carbon::now();

        $largest_student_id = StudentRegister::where('reg_id', 'like', 'ক-%')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('reg_id')
            ->map(function ($id) {
                return (int) preg_replace("/[^0-9]/", "", $id);
            })
            ->max();

        $largest_student_id = $largest_student_id ?? 0;
        $next_id = $largest_student_id + 1;

        $formatted_id = str_pad($next_id, 3, '0', STR_PAD_LEFT);
        return 'ক-' . $formatted_id;
    }



     
}
