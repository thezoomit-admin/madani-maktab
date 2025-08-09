<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use Illuminate\Http\Request;

class ActionLogReportController extends Controller
{
    public function index(Request $request){
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = ActionLog::with('user:id,name,email')  
                ->select(
                    'id',
                    'user_id',
                    'method',
                    'route',
                    'action',
                    'ip_address',
                    'user_agent',
                    'browser_url',
                    'accessed_at',
                    'hostname',
                    'platform',
                    'uptime',
                    'request_status_code',
                    'response_status_code',
                    'timestamp'
                );

            // ====== Filters ======
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->filled('from') && $request->filled('to')) {
                $query->whereBetween('timestamp', [
                    $request->input('from'),
                    $request->input('to')
                ]);
            }

            if ($request->filled('method')) {
                $query->where('method', strtoupper($request->input('method')));
            }

            if ($request->filled('route')) {
                $query->where('route', $request->input('route'));
            }

            if ($request->filled('ip')) {
                $query->where('ip_address', $request->input('ip'));
            }

            if ($request->filled('status')) {
                $query->where('response_status_code', $request->input('status'));
            }

            if ($request->filled('action')) {
                $query->where('action', 'like', '%' . $request->input('action') . '%');
            }

            if ($request->filled('platform')) {
                $query->where('platform', $request->input('platform'));
            }

            // Pagination সহ ডাটা আনা
            $logs = $query->orderBy('timestamp', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform data
            $modified = $logs->getCollection()->transform(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->name ?? null,
                    'method' => $log->method,
                    'route' => $log->route,
                    'action' => $log->action,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'browser_url' => $log->browser_url,
                    'accessed_at' => $log->accessed_at,
                    'hostname' => $log->hostname,
                    'platform' => $log->platform,
                    'uptime' => $log->uptime,
                    'request_status_code' => $log->request_status_code,
                    'response_status_code' => $log->response_status_code,
                    'timestamp' => $log->timestamp,
                ];
            });

            $logs->setCollection($modified->values());

            return response()->json([
                'data' => $logs->items(),
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
