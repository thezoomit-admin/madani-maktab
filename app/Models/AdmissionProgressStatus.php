<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionProgressStatus extends Model
{
    use HasFactory;  
    protected $fillable = [
        'user_id', 
        'is_passed_age',
        'is_interview_scheduled',
        'is_passed_interview',
        'is_invited_for_trial',
        'is_passed_trial',
    ];
}
