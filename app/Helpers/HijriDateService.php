<?php

namespace App\Helpers;

use App\Models\HijriMonth;
use App\Enums\ArabicMonth;
use Carbon\Carbon;

class HijriDateService
{
   
    // public function getHijri(string $gregorianDate): string
    // {
    //     $parsedDate = Carbon::parse($gregorianDate)->toDateString(); 
    //     $hijriMonth = HijriMonth::whereDate('start_date', '<=', $parsedDate)
    //         ->whereDate('end_date', '>=', $parsedDate)
    //         ->first();

    //     if (!$hijriMonth) {
    //         return 'Date not in any Hijri month';
    //     }

    //     $startDate = Carbon::parse($hijriMonth->start_date);
    //     $currentDate = Carbon::parse($parsedDate);

    //     $day = $startDate->diffInDays($currentDate) + 1;
    //     $monthNumber = $hijriMonth->month;   
    //     $year = $hijriMonth->year; 
    //     return "{$day}/{$monthNumber}/{$year}";
    // } 

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
        $monthNumber = $hijriMonth->month;  
        $year = $hijriMonth->year;

        $monthNames = ArabicMonth::values();
        $monthName = $monthNames[$monthNumber] ?? 'অজানা মাস';  

        return "{$day} {$monthName}, {$year} হিজরি";
    }

}
