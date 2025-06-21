<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AttendanceSyncController extends Controller
{  
    public function sync()
    {
        $start_time = Carbon::now()->subMinutes(5)->format('H:i:s');
        $end_time = Carbon::now()->format('H:i:s'); 
        $response = Http::post('https://rumytechnologies.com/rams/json_api', [
            'operation'   => 'fetch_log',
            'auth_user'   => 'madani',
            'auth_code'   => 't1zpl8zxe1m1m6iexbb2ijz47tseg54',
            'start_date'  => now()->format('Y-m-d'),
            'end_date'    => now()->format('Y-m-d'),
            'start_time'  => $start_time,
            'end_time'    => $end_time,
        ]);

        $data = $response->json();

        if (!isset($data['log'])) {
            return response()->json(['message' => 'No log data found.'], 404);
        }

        foreach ($data['log'] as $log) {   
            $exists = Attendance::where('in_access_id', $log['access_id'])
                ->orWhere('out_access_id', $log['access_id'])
                ->exists();

          

            if ($exists) { 
                continue;
            }

              
            // Get user by reg_id
            $user = User::where('reg_id', $log['registration_id'])->first();
 
            if ($user) {
                $accessDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $log['access_date'] . ' ' . $log['access_time']);

                // Check last attendance of this user for today
                $lastAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('in_time', $accessDateTime->toDateString())
                    ->orderByDesc('in_time')
                    ->first();

                if ($lastAttendance && $lastAttendance->out_time === null) {
                    // Fill as out_time
                    $lastAttendance->update([
                        'out_time'        => $accessDateTime,
                        'out_access_id'   => $log['access_id'],
                    ]);
                } else {
                    // Create new as in_time
                    Attendance::create([
                        'user_id'        => $user->id,
                        'reg_id'         => $log['registration_id'],
                        'in_time'        => $accessDateTime,
                        'in_access_id'   => $log['access_id'],
                        'out_time'       => null,
                        'out_access_id'  => null,
                        'comment'        => null,
                        'comment_by'     => null,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Attendance synced at ' . now()]);
    }
}
