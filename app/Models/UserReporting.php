<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReporting extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'reporting_user_id',
        'start_date',
        'end_date',
    ];
}
