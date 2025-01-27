<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id', 
        'is_send_fail_message',
        'is_send_general_pass_message',
        'is_send_interview_pass_message',
        'is_send_final_pass_message',
    ];
}
