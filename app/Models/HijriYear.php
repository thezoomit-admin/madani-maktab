<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HijriYear extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'start_date', 'end_date'];
}
