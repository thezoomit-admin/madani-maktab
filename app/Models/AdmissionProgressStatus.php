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
        'is_send_step_2_link',
        'is_registration_complete',
        'is_interview_scheduled',
        'is_passed_interview',
        'is_invited_for_trial',
        'is_passed_trial',
        'is_send_fail_message',
        'is_send_final_pass_message',
        'is_print_profile'
    ];
}
