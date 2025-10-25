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

    public static function getYearRange($year)
    {
        $range = self::where('year', $year)
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        if (!$range || !$range->start_date || !$range->end_date) {
            return null;
        }

        return [
            'start_date' => $range->start_date,
            'end_date' => $range->end_date,
        ];
    }   
}
