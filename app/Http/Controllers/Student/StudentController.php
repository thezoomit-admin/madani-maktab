<?php

namespace App\Http\Controllers\Student;

use App\Enums\Department;
use App\Enums\FeeType;
use App\Enums\KitabSession;
use App\Enums\MaktabSession;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Attendance;
use App\Models\Enrole;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\TeacherComment;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);  

            $active_month = HijriMonth::where('is_active', true)->first();
            $year = $request->input('year', $active_month->year ?? 1446);
            $session = $request->input('session');

            $students = Student::with([
                    'user:id,name,reg_id,phone,profile_image,blood_group',
                    'enroles' => function ($query) use ($year) {
                        $query->where('year', $year)
                            ->where('status', 1)
                            ->select('id','roll_number', 'student_id', 'department_id', 'session', 'fee_type', 'status', 'year');
                    }
                ])
                ->when($request->input('jamaat'), function ($query, $jamaat) {
                    $query->where('jamaat', $jamaat);
                })
                ->when($request->filled('session'), function ($query) use ($session) {
                    $query->whereHas('enroles', function ($q) use ($session) {
                        $q->whereIn('id', function ($subquery) {
                            $subquery->selectRaw('MAX(id)')
                                    ->from('enroles')
                                    ->groupBy('student_id');
                        });

                        if ($session >= 1 && $session <= 5) {
                            $q->where('department_id', 1)
                            ->where('session', $session);
                        } else {
                            if ($session != 0) {
                                $session = $session - 5;
                            }
                            $q->where('department_id', 2)
                            ->where('session', $session);
                        }
                    });
                })
                ->whereHas('user', function ($query) use ($request) {
                    if ($request->filled('blood_group')) {
                        $query->where('blood_group', $request->input('blood_group'));
                    }
                    if ($request->filled('name')) {
                        $query->where('name', $request->input('name'));
                    }
                    if ($request->filled('reg_id')) {
                        $query->where('reg_id', $request->input('reg_id'));  
                    }
                })
                ->whereHas('enroles', function ($query) use ($request) {
                    if ($request->filled('roll_number')) {
                        $query->where('roll_number', $request->input('roll_number'));
                    }
                })
                ->select('id', 'user_id', 'jamaat', 'average_marks', 'status')
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $userIds = $students->pluck('user_id')->unique();

            $attendances = Attendance::whereIn('user_id', $userIds)
                ->select('id', 'user_id', 'in_time', 'out_time')
                ->latest('in_time')
                ->get()
                ->groupBy('user_id')
                ->map(fn($records) => $records->first());

            // Transform and enrich student data
            $modified = $students->getCollection()->transform(function ($student) use ($attendances) {
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

                $attendance = $attendances->get($user->id);
                $is_present = $attendance && $attendance->out_time === null;

                return [
                    'id' => $student->id,
                    'user_id' => $user->id,
                    'roll_number' => $enrole->roll_number,
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

                    'department_id' => $departmentId,  
                    'session_id' => $sessionId,     
                    'is_present' => $is_present,
                ];
            });

            $modified = $modified->sortBy([
                ['department_id', 'asc'],
                ['session_id', 'asc']
            ]);

            $modified = $modified->map(function ($item) {
                unset($item['department_id'], $item['session_id']);
                return $item;
            });

            $students->setCollection($modified->values());

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

    public function delete($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return error_response(null, 404, 'স্টুডেন্ট খুঁজে পাওয়া যায়নি।');
        }

        $payment_transaction = PaymentTransaction::where('student_id', $id)->first();
        if ($payment_transaction) {
            return error_response(null, 403, 'এই স্টুডেন্টের পেমেন্ট ট্রান্সাকশন রয়েছে, তাই ডিলিট করা যাবে না।');
        }

        $user = User::find($student->user_id);
        if (!$user) {
            return error_response(null, 404, 'সংশ্লিষ্ট ইউজার খুঁজে পাওয়া যায়নি।');
        }

        DB::beginTransaction();
        try { 
            Enrole::where('student_id', $student->id)->delete();
    
            TeacherComment::where('student_id', $user->id)->delete();
            Payment::where('user_id', $user->id)->delete();
            Admission::where('user_id', $user->id)->update(['status' => 0]);
    
            $user->reg_id = null;
            $user->save();
    
            $student->delete();

            DB::commit();
            return success_response(null, 'স্টুডেন্ট ও সংশ্লিষ্ট তথ্য সফলভাবে ডিলিট করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, 'ডিলিট করার সময় একটি ত্রুটি ঘটেছে: ' . $e->getMessage());
        }
    } 

    public function updateRoll($id, Request $request)
    {
        $enrole = Enrole::where('status', 1)
            ->whereHas('user', function ($q) use ($id) {
                $q->where('id', $id);
            })
            ->first();  
        if (!$enrole) {
            return error_response('Enrollment not found', 404);
        }

        $enrole->roll_number = $request->roll_number;
        $enrole->save(); 
        return success_response(null, "Roll number updated");
    }


}
