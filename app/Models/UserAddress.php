<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory; 
    protected $fillable = [
        'user_id',
        'student_id',
        'address_type',
        'house_or_state',
        'vaillage_or_area',
        'post_office',
        'upazila_thana', 
        'district',
        'division', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(StudentRegister::class);
    }
}
