<?php  
use Illuminate\Support\Str;
if (!function_exists('success_response')) {
    function success_response($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->header('X-Request-ID') ?: uniqid(),
        ], $statusCode);
    }
}

if (!function_exists('error_response')) {
    function error_response( $errors = null, $statusCode = 400, $message = 'An error occurred')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String(),
            'request_id' => request()->header('X-Request-ID') ?: uniqid(),
        ], $statusCode);
    }
}



if (!function_exists('getSlug')) {
    function getSlug($model, $title, $column = 'slug', $separator = '-') {
        $slug         = Str::slug($title);
        $originalSlug = $slug;
        $count        = 1;

        while ($model::where($column, $slug)->exists()) {
            $slug = $originalSlug . $separator . $count;
            $count++;
        }

        return $slug;
    }
}

if (!function_exists('enum_name')) {
    function enum_name($enumClass, $id)
    {
        if (!class_exists($enumClass)) {
            return null;
        }

        $values = $enumClass::values();
        return $values[$id] ?? null;
    }
}


if (!function_exists('get_current_role')) {
    /**
     * Get current role of a user safely
     *
     * @param int $userId
     * @return array|null
     */
    function get_current_role($userId)
    {
        // Step 1: Ensure $userId is valid integer
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }

        $userId = (int)$userId;
        $today = now()->toDateString();

        try {
            $role = \App\Models\EmployeeRole::with('role')
                ->where('user_id', $userId)
                ->whereDate('start_date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
                })
                ->orderByDesc('start_date')
                ->first();
        } catch (\Exception $e) {
            // Catch any DB or query errors and return null
            return null;
        }

        // If no role found, return null
        if (!$role) {
            return null;
        }

        // Return safe array, handle missing relation
        return [
            'role_id'   => $role->role_id ?? null,
            'role_name' => $role->role?->name ?? null,
            'start_date'=> $role->start_date ?? null,
            'end_date'  => $role->end_date ?? null,
        ];
    }
}


if (!function_exists('get_permissions')) {
   
    function get_permissions(int $userId, ?string $userType = null): array
    {
        // Students have fixed permission set
        if ($userType === 'student') {
            return ['student'];
        }

        // Try current role from employee_roles
        $currentRole = get_current_role($userId);
        if ($currentRole && isset($currentRole['role_id'])) {
            $roleModel = \App\Models\Role::with('permissions')->find($currentRole['role_id']);
            if ($roleModel) {
                return $roleModel->permissions->pluck('slug')->toArray();
            }
        }
        return [];
    }
}

if (!function_exists('hijri_month_name')) {
    /**
     * Resolve Hijri month name from hijri_months table id.
     */
    function hijri_month_name(?int $hijriMonthId): ?string
    {
        if (empty($hijriMonthId)) {
            return null;
        }

        static $cache = [];

        if (array_key_exists($hijriMonthId, $cache)) {
            return $cache[$hijriMonthId];
        }

        $record = \App\Models\HijriMonth::select('month')->find($hijriMonthId);
        if (!$record) {
            $cache[$hijriMonthId] = null;
            return null;
        }

        $months = \App\Enums\ArabicMonth::values();
        $cache[$hijriMonthId] = $months[$record->month] ?? null;

        return $cache[$hijriMonthId];
    }
}

if (!function_exists('image_url')) {
   
    function image_url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $baseUrl = env('APP_IMAGE_URL');
        
        if ($baseUrl) { 
            $baseUrl = rtrim($baseUrl, '/');
            $path = ltrim($path, '/');
            return $baseUrl . '/' . $path;
        }
 
        return asset($path);
    }
}

