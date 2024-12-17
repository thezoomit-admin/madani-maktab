<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreAdmissionTrial extends Model
{
    use HasFactory; 

    protected $fillable = [
        'candidate_id',  
        'teacher_id',       
        'requested_at',  
        'attended_at',   
        'status',  
        'result',
        'note',   
    ];
    
}
