<?php

namespace App\Http\Controllers\Student;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\HijriMonth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function attendance(Request $request, $reg_id = null)
    {
        $user = Auth::user();
        $reg_id = $reg_id ?? $user->reg_id;

        $month = $request->filled('month_id')
            ? \App\Models\HijriMonth::find($request->month_id)
            : null;
 
        if ($month) {
            $startDate = Carbon::parse($month->start_date)->startOfDay();
            $endDate = Carbon::parse($month->end_date)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        $attendances = Attendance::where('reg_id', $reg_id)
            ->whereBetween('in_time', [$startDate, $endDate])
            ->orderBy('in_time', 'asc')
            ->get();

        $hijriService = new HijriDateService();

        $attendance = $attendances->map(function ($record) use ($hijriService) {
            $inTime = Carbon::parse($record->in_time);
            $outTime = $record->out_time ? Carbon::parse($record->out_time) : null;

            return [
                'id'        => $record->id,
                'in_date'  => $hijriService->getHijri($inTime->toDateString()),
                'in_time'  => $inTime->format('H:i:s'),
                'out_date' => $outTime ? $hijriService->getHijri($outTime->toDateString()) : null,
                'out_time' => $outTime ? $outTime->format('H:i:s') : null,
            ];
        });

        return success_response($attendance->reverse()->values());
    }

}
