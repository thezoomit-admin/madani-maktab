<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceSyncController extends Controller
{  
    public function sync()
    {
        try {
            $start_time = Carbon::now()->subMinutes(50)->format('H:i:s');
            $end_time = Carbon::now()->format('H:i:s'); 
            $response = Http::post('https://rumytechnologies.com/rams/json_api', [
                'operation'   => 'fetch_log',
                'auth_user'   => 'madani',
                'auth_code'   => 't1zpl8zxe1m1m6iexbb2ijz47tseg54',
                'start_date'  => "2025-10-28",
                'end_date'    => now()->format('Y-m-d'),
                'start_time'  => $start_time,
                'end_time'    => $end_time,
            ]);

            if (!$response->successful()) {
                Log::error('Sync failed: API request unsuccessful', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json(['message' => 'API request failed.'], 500);
            }

            $data = $response->json();

            if (empty($data['log'])) {
                return response()->json(['message' => 'No log data found.'], 404);
            }

            foreach ($data['log'] as $log) {
                $exists = Attendance::where('in_access_id', $log['access_id'])
                    ->orWhere('out_access_id', $log['access_id'])
                    ->exists();

                if ($exists) continue;

                $user = User::where('reg_id', $log['registration_id'])->first();
    
                if (!$user) continue;

                $accessDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $log['access_date'].' '.$log['access_time']);

                $lastAttendance = Attendance::where('user_id', $user->id)
                    ->whereNull('out_time')  
                    ->orderByDesc('id')
                    ->first();

                if ($lastAttendance) {
                    $lastAttendance->update([
                        'out_time'      => $accessDateTime,
                        'out_access_id' => $log['access_id'],
                    ]);

                    $user->update(['is_present' => 0]);
                } else {
                    Attendance::create([
                        'user_id'       => $user->id,
                        'reg_id'        => $log['registration_id'],
                        'in_time'       => $accessDateTime,
                        'in_access_id'  => $log['access_id'],
                        'out_time'      => null,
                        'out_access_id' => null,
                        'comment'       => null,
                        'comment_by'    => null,
                    ]);

                    $user->update(['is_present' => 1]);
                }
            }
    

            return response()->json(['message' => 'Attendance synced successfully at ' . now()]);
        } catch (\Exception $e) {
            Log::error('Attendance sync error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error during sync', 'error' => $e->getMessage()], 500);
        }
    } 
}
