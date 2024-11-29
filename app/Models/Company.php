<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory; 

    protected $fillable = [
        'name',
        'website',
        'address',
        'logo',
        'primary_color',
        'secondary_color',
        'founded_date',
        'is_active',
        'category_id',
    ];
}
