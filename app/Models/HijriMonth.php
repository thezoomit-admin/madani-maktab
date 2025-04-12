<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HijriMonth extends Model
{
    use HasFactory;
    protected $fillable = [
        'year',
        'month',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];
    
}
