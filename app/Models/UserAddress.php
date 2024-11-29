<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory; 
    protected $fillable = [
        'user_id',
        'country_id',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'village_id',
        'post_code',
        'address',
    ];
}
