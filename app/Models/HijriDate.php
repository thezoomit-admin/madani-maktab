<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HijriDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'hijri_year_id',
        'hijri_month_id',
        'start_date',
        'end_date',
    ];

    public function year(){
        return $this->belongsTo(HijriYear::class,'hijri_year_id');
    }

    public function month(){
        return $this->belongsTo(HijriMonth::class,'hijri_month_id');
    }
}
