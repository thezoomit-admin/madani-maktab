<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AttendanceService
{
    protected Collection $logs;

    public function __construct()
    { 
        $this->logs = collect();
    }

    /**
     * একবার API থেকে attendance logs নিয়ে মেমরিতে রাখবে
     * 
     * @param Carbon|null $date (যদি null হয়, তাহলে আজকের ডেটা নিবে)
     * @return void
     */
    public function fetchLogs(Carbon $date = null): void
    {
        $date = $date ?? Carbon::today();

        $payload = [
            'operation'  => 'fetch_log',
            'auth_user'  => env('STELLAR_AUTH_USER'),
            'auth_code'  => env('STELLAR_AUTH_CODE'),
            'start_date' => $date->toDateString(),
            'end_date'   => $date->toDateString(),
            'start_time' => '00:00:00',
            'end_time'   => '23:59:59',
        ];

        $response = Http::post(env('STELLAR_API_BASE_URL'), $payload);

        if ($response->successful()) {
            $this->logs = collect($response->json('log') ?? []);
        } else { 
            $this->logs = collect();
        }
    }
 
    public function isStudentPresent(string $registrationId): bool
    {
        $studentLogs = $this->logs
            ->where('registration_id', (string) $registrationId)
            ->sortBy('access_time')
            ->values(); 
        return $studentLogs->count() % 2 !== 0;
    }
}
