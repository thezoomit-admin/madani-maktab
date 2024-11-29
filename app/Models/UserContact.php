<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    use HasFactory; 
    protected $fillable = [
        'user_id',
        'name',
        'office_phone',
        'personal_phone',
        'office_email',
        'personal_email',
        'imo_number',
        'facebook_id',
        'emergency_contact_number',
        'emergency_contact_person',
    ];
}
