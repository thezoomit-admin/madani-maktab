<?php

namespace App\Helpers;

use App\Models\HijriMonth;
use App\Enums\ArabicMonth;
use Carbon\Carbon;

class HijriDateService
{
   
    public function getHijri(string $gregorianDate): string
    {
        $parsedDate = Carbon::parse($gregorianDate)->toDateString(); 
        $hijriMonth = HijriMonth::whereDate('start_date', '<=', $parsedDate)
            ->whereDate('end_date', '>=', $parsedDate)
            ->first();

        if (!$hijriMonth) {
            return 'Date not in any Hijri month';
        }

        $startDate = Carbon::parse($hijriMonth->start_date);
        $currentDate = Carbon::parse($parsedDate);

        $day = $startDate->diffInDays($currentDate) + 1;
        $monthNumber = $hijriMonth->month;   // Already a number from DB
        $year = $hijriMonth->year; 
        return "{$day}/{$monthNumber}/{$year}";
    }

}
