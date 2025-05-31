<?php

namespace App\Http\Controllers\Student;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
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
                ? HijriMonth::find($request->month_id) 
                : null;
    
            if ($month) {
                $startDate = $month->start_date;
                $endDate = $month->end_date;
            } else {
                $endDate = Carbon::now();
                $startDate = $endDate->copy()->subDays(30);
            }

            $data = [
                'operation'  => 'fetch_log',
                'auth_user'  => env('STELLAR_AUTH_USER'),
                'auth_code'  => env('STELLAR_AUTH_CODE'),
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'start_time' => '00:00:00',
                'end_time'   => '23:59:59',
            ];

            $response = Http::post(env('STELLAR_API_BASE_URL'), $data);

            if (!$response->successful()) {
                return response()->json([
                    'registration_id' => $reg_id,
                    'attendance' => [],
                    'error' => 'Failed to fetch logs',
                ], 500);
            }

            $logs = collect($response->json('log') ?? [])
                ->filter(fn($log) => (string) $log['registration_id'] === (string) $reg_id)
                ->sortBy([
                    ['access_date', 'asc'],
                    ['access_time', 'asc'],
                ])
                ->values();

            $attendance = [];
            for ($i = 0; $i < $logs->count(); $i += 2) {
                $inLog = $logs[$i];
                $outLog = $logs[$i + 1] ?? null;

                $attendance[] = [
                    'date'     => app(HijriDateService::class)->getHijri($inLog['access_date']),
                    'in_time'  => $inLog['access_time'],
                    'out_time' => $outLog['access_time'] ?? null,
                ];
            }

            return response()->json([
                'registration_id' => $reg_id,
                'attendance' =>array_reverse($attendance),
            ]);
        }  
}
