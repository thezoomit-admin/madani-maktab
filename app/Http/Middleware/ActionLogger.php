<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActionLog;

class ActionLogger
{
    public function handle(Request $request, Closure $next)
    { 
        $response = $next($request);
 
        if (!$request->user()) {
            return $response;
        }  
        
        $clientDetails = [];
        if ($request->header('x-client-details')) {
            try {
                $clientDetails = json_decode($request->header('x-client-details'), true);
            } catch (\Exception $e) {
                \Log::warning('Invalid x-client-details JSON');
            }
        }

        $ipAddress = $request->header('x-forwarded-for') 
            ?? $request->ip();

        // Server uptime format
        $uptime = $this->formatUptime();

        // Action text
        $action = $request->header('x-action') 
            ?? $request->method() . ' ' . $request->path();

        // Payload তৈরি
        $payload = [
            'user_id' => $request->user()->id,
            'method' => $request->method(),
            'route' => '/' . ltrim($request->path(), '/'),
            'action' => $action,
            'ip_address' => $clientDetails['ipAddress'] ?? $ipAddress,
            'user_agent' => $clientDetails['userAgent'] ?? $request->userAgent(),
            'browser_url' => $clientDetails['browserUrl'] ?? '',
            'accessed_at' => $clientDetails['accessedAt'] ?? now(),
            'hostname' => gethostname(),
            'platform' => php_uname('s'),
            'uptime' => $uptime,
            'request_status_code' => 200,  
            'response_status_code' => $response->getStatusCode(),
            'timestamp' => now(),
        ];


        // DB-তে সেভ
        try {
            ActionLog::create($payload);
        } catch (\Exception $e) {
            \Log::error("❌ Failed to save action log: " . $e->getMessage());
        }

        return $response;
    }

    private function formatUptime(): string
    {
        $uptimeSeconds = (int) shell_exec('awk \'{print $1}\' /proc/uptime');
        $hours = floor($uptimeSeconds / 3600);
        $minutes = floor(($uptimeSeconds % 3600) / 60);
        return "{$hours} hours {$minutes} minutes";
    }
}
