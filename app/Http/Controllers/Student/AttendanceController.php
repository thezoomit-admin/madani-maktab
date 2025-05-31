<?php

namespace App\Http\Controllers\Student;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\HijriMonth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
      public function attendance(Request $request, $reg_id)
    {
        $month_id = $request->month_id;
        if($month_id){
            $month = HijriMonth::find($month_id); 
        }else{
            $month = HijriMonth::where('is_active',true)?->first(); 
        }
        if($month){
            $startDate = $month->start_date;
            $endDate = $month->end_date;
        }else{
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->toDateString();
        }
      

        $data = [
            'operation'  => 'fetch_log',
            'auth_user'  => env('STELLAR_AUTH_USER'),
            'auth_code'  => env('STELLAR_AUTH_CODE'),
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'start_time' => '00:00:00',
            'end_time'   => '23:59:59', 
        ];

        $response = Http::post(env('STELLAR_API_BASE_URL'), $data);

        if ($response->successful()) { 
 
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
                    'date'     =>app(HijriDateService::class)->getHijri($inLog['access_date']),
                    'in_time'  => $inLog['access_time'],
                    'out_time' => $outLog['access_time'] ?? null, 
                ];
            }

            return response()->json([
                'registration_id' => $reg_id,
                'attendance'      => $attendance,
            ]);
        }

        return response()->json([
            'registration_id' => $reg_id,
            'attendance'      => [],
            'error'           => 'Failed to fetch logs',
        ], 500);
    }

}
