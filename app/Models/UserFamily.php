<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFamily extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'deeni_steps',
        'is_follow_porada',
        'is_shariah_compliant',
        'motivation',
        'info_src',
        'first_contact',
        'preparation',
        'is_clean_lang',
        'future_plan',
        'years_at_inst',
        'reason_diff_edu',
    ];

    
}
