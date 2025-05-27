<?php

namespace App\Http\Controllers\Student;

use App\Enums\Department;
use App\Enums\FeeType;
use App\Enums\KitabSession;
use App\Enums\MaktabSession;
use App\Http\Controllers\Controller;
use App\Models\HijriMonth;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1); 
            $active_month = HijriMonth::where('is_active', true)->first();
            $year = $request->input('year', $active_month->year??1446);

            $students = Student::with([
                'user:id,name,reg_id,phone,profile_image,blood_group',
                'enroles' => function ($query) use ($year) {
                    $query->where('year', $year)->where('status',1)
                        ->select('id', 'student_id', 'department_id', 'session', 'fee_type', 'status', 'year');
                }
            ])
            ->when($request->input('jamaat'), function ($query, $jamaat) {
                $query->where('jamaat', $jamaat);
            })
            ->whereHas('user', function ($query) use ($request) {
                if ($request->filled('blood_group')) {
                    $query->where('blood_group', $request->input('blood_group'));
                }
            })
            ->select('id', 'user_id', 'jamaat', 'average_marks', 'status')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

            $modified = $students->getCollection()->transform(function ($student) {
                $user = $student->user;
                $enrole = $student->enroles->first();

                $departmentId = $enrole->department_id ?? null;
                $sessionId = $enrole->session ?? null;
                $feeTypeId = $enrole->fee_type ?? null;

                $sessionName = null;
                if ($departmentId === Department::Maktab) {
                    $sessionName = enum_name(MaktabSession::class, $sessionId);
                } elseif ($departmentId === Department::Kitab) {
                    $sessionName = enum_name(KitabSession::class, $sessionId);
                }

                return [
                    'id' => $student->id,
                    'user_id' => $user->id,
                    'reg_id' => $user->reg_id,
                    'jamaat' => $student->jamaat,
                    'average_marks' => $student->average_marks,

                    'name' => $user->name ?? null,
                    'phone' => $user->phone ?? null,
                    'profile_image' => $user->profile_image ?? null,
                    'blood_group' => $user->blood_group ?? null,    
                    'department' => enum_name(Department::class, $departmentId), 
                    'session' => $sessionName, 
                    'fee_type' => enum_name(FeeType::class, $feeTypeId),
                    'status' => $enrole->status ?? null,
                    'year' => $enrole->year ?? null,
                ];
            });

            $students->setCollection($modified);

            return success_response([
                'data' => $students->items(),
                'pagination' => [
                    'total' => $students->total(),
                    'per_page' => $students->perPage(),
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return error_response(null, 500, $e->getMessage());
        }
    }  


    //   public function index(Request $request)
    // {
    //     try {
    //         $perPage = $request->input('per_page', 10);
    //         $page = $request->input('page', 1);
            
    //         $active_month = HijriMonth::where('is_active', true)->first();
    //         $year = $request->input('year', $active_month->year ?? 1446);
    
    //         $attendanceService = new AttendanceService();
    //         $attendanceService->fetchLogs(); 
    
    //         $students = Student::with([
    //             'user:id,name,reg_id,phone,profile_image,blood_group',
    //             'enroles' => function ($query) use ($year) {
    //                 $query->where('year', $year)
    //                     ->where('status', 1)
    //                     ->select('id', 'student_id', 'department_id', 'session', 'fee_type', 'status', 'year');
    //             }
    //         ])
    //         ->when($request->input('jamaat'), function ($query, $jamaat) {
    //             $query->where('jamaat', $jamaat);
    //         })
    //         ->whereHas('user', function ($query) use ($request) {
    //             if ($request->filled('blood_group')) {
    //                 $query->where('blood_group', $request->input('blood_group'));
    //             }
    //         })
    //         ->select('id', 'user_id', 'jamaat', 'average_marks', 'status')
    //         ->orderBy('id', 'desc')
    //         ->paginate($perPage, ['*'], 'page', $page);
    
    //         $modified = $students->getCollection()->transform(function ($student) use ($attendanceService) {
    //             $user = $student->user;
    //             $enrole = $student->enroles->first();

    //             $departmentId = $enrole->department_id ?? null;
    //             $sessionId = $enrole->session ?? null;
    //             $feeTypeId = $enrole->fee_type ?? null;

    //             $sessionName = null;
    //             if ($departmentId === Department::Maktab) {
    //                 $sessionName = enum_name(MaktabSession::class, $sessionId);
    //             } elseif ($departmentId === Department::Kitab) {
    //                 $sessionName = enum_name(KitabSession::class, $sessionId);
    //             }

    //             return [
    //                 'id' => $student->id,
    //                 'user_id' => $user->id,
    //                 'reg_id' => $user->reg_id,
    //                 'jamaat' => $student->jamaat,
    //                 'average_marks' => $student->average_marks,

    //                 'name' => $user->name ?? null,
    //                 'phone' => $user->phone ?? null,
    //                 'profile_image' => $user->profile_image ?? null,
    //                 'blood_group' => $user->blood_group ?? null,
    //                 'department' => enum_name(Department::class, $departmentId),
    //                 'session' => $sessionName,
    //                 'fee_type' => enum_name(FeeType::class, $feeTypeId),
    //                 'status' => $enrole->status ?? null,
    //                 'year' => $enrole->year ?? null,
    
    //                 'is_present' => $attendanceService->isStudentPresent($user->reg_id),
    //             ];
    //         });

    //         $students->setCollection($modified);

    //         return success_response([
    //             'data' => $students->items(),
    //             'pagination' => [
    //                 'total' => $students->total(),
    //                 'per_page' => $students->perPage(),
    //                 'current_page' => $students->currentPage(),
    //                 'last_page' => $students->lastPage(),
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return error_response(null, 500, $e->getMessage());
    //     }
    // }
}
