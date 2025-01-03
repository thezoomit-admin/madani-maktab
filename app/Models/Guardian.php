<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'student_id',
        'guardian_name',
        'guardian_relation',
        'guardian_occupation_details',
        'guardian_education', 
        'children_count',
        'child_education',
        'contact_number_1',
        'contact_number_2',
        'whatsapp_number',
        'same_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

  
    
}
