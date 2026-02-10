<?php

namespace App\Http\Controllers\Student;

use App\Enums\Department;
use App\Enums\FeeReason;
use App\Enums\FeeType;
use App\Enums\KitabSession;
use App\Enums\MaktabSession;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionProgressStatus;
use App\Models\Attendance;
use App\Models\Enrole;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\TeacherComment;
use App\Models\User;
use App\Models\FeeSetting;
use App\Services\AttendanceService;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\PaginateTrait;


class StudentController extends Controller
{
    use PaginateTrait;

    public function index(Request $request)
    {
        try { 
            $year = $request->input('year');
            $session = $request->input('session');

            // 🟢 মূল query
            $query = Student::with(['user:id,name,reg_id,phone,profile_image,blood_group,is_present', 'enroles'])
                ->addSelect([
                    'latest_enrole_id' => Enrole::select('id')
                        ->whereColumn('student_id', 'students.id')
                        ->orderByDesc('id')
                        ->limit(1),
                ])
                ->when($request->input('jamaat'), function ($query, $jamaat) {
                    $query->where('jamaat', $jamaat);
                })
                ->when($request->filled('session'), function ($query) use ($session, $year) {
                    $query->whereHas('enroles', function ($q) use ($session, $year) {
                        // If year is provided, filter by year (not status)
                        if ($year) {
                            $q->where('year', $year)
                            ->whereIn('id', function ($subquery) use ($year) {
                                $subquery->selectRaw('MAX(id)')
                                        ->from('enroles')
                                        ->where('year', $year)
                                        ->groupBy('student_id');
                            });
                        } else {
                            // If year is null, filter by active enrollment (status = 1)
                            $q->where('status', 1)
                            ->whereIn('id', function ($subquery) {
                                $subquery->selectRaw('MAX(id)')
                                        ->from('enroles')
                                        ->groupBy('student_id');
                            });
                        }

                        // ✅ session অনুযায়ী filter
                        if ($session >= 1 && $session <= 5) {
                            $q->where('department_id', 1)->where('session', $session);
                        } else {
                            if ($session != 0) {
                                $session = $session - 5;
                            }
                            $q->where('department_id', 2)->where('session', $session);
                        }
                    });
                })
                ->when(!$request->filled('session'), function ($query) use ($year) {
                    // If no session filter, apply year or status filter
                    if ($year) {
                        // Filter by year
                        $query->whereHas('enroles', function ($q) use ($year) {
                            $q->where('year', $year)
                            ->whereIn('id', function ($subquery) use ($year) {
                                $subquery->selectRaw('MAX(id)')
                                        ->from('enroles')
                                        ->where('year', $year)
                                        ->groupBy('student_id');
                            });
                        });
                    } else {
                        // Filter by active enrollment (status = 1)
                        $query->whereHas('enroles', function ($q) {
                            $q->where('status', 1)
                            ->whereIn('id', function ($subquery) {
                                $subquery->selectRaw('MAX(id)')
                                        ->from('enroles')
                                        ->groupBy('student_id');
                            });
                        });
                    }
                })
                ->whereHas('user', function ($query) use ($request) { 
                    if ($request->filled('name')) {
                        $query->where('name', 'like', '%' . $request->input('name') . '%');
                    } 
                    if ($request->has('reg_id')) {
                        $regId = trim((string) $request->input('reg_id')); // string cast + trim
                        $query->where('reg_id', $regId);
                    } 
                    if ($request->filled('blood_group')) {
                        $query->where('blood_group', $request->input('blood_group'));
                    }
                })
                
                ->whereHas('enroles', function ($query) use ($request, $year) {
                    if ($request->filled('roll_number')) {
                        $query->where('roll_number', $request->input('roll_number'));
                    }
                    // Apply year filter if provided
                    if ($year) {
                        $query->where('year', $year);
                    }
                })
                ->select('id', 'user_id', 'jamaat', 'average_marks', 'status')
                ->orderBy('id', 'desc');

            // 🟢 Pagination
            $paginated = $this->paginateQuery($query, $request);

            // 🟢 Transform data
            $paginated['data'] = collect($paginated['data'])->map(function ($student) use ($year) {
                $user = $student->user;

                // ✅ If year is provided, get enrollment by year, otherwise get active enrollment
                if ($year) {
                    $enrole = $student->enroles
                        ->where('year', $year)
                        ->sortByDesc('id')
                        ->first();
                } else {
                    // শুধুমাত্র active enrolment (status = 1)
                    $enrole = $student->enroles
                        ->where('status', 1)
                        ->sortByDesc('id')
                        ->first();
                }

                $departmentId = $enrole->department_id ?? null;
                $sessionId = $enrole->session ?? null;
                $feeTypeId = $enrole->fee_type ?? null;

                // ✅ sessionName active enrolment থেকেই আসবে
                $sessionName = null;
                if ($departmentId === Department::Maktab) {
                    $sessionName = enum_name(MaktabSession::class, $sessionId);
                } elseif ($departmentId === Department::Kitab) {
                    $sessionName = enum_name(KitabSession::class, $sessionId);
                }

                return [
                    'id' => $student->id,
                    'user_id' => $user->id ?? null,
                    'roll_number' => $enrole->roll_number ?? null,
                    'reg_id' => $user->reg_id ?? null,
                    'jamaat' => $student->jamaat,
                    'average_marks' => $student->average_marks,
                    'name' => $user->name ?? null,
                    'phone' => $user->phone ?? null,
                    'profile_image' => $user->profile_image,
                    'blood_group' => $user->blood_group ?? null,
                    'department' => enum_name(Department::class, $departmentId),
                    'session' => $sessionName,
                    'fee_type' => enum_name(FeeType::class, $feeTypeId),
                    'status' => $enrole->status ?? null,
                    'year' => $enrole->year ?? null,
                    'is_present' => $user->is_present ?? false,
                ];
            })->values();

            return success_response($paginated);

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

        // $payment_transaction = PaymentTransaction::where('student_id', $id)->first();
        // if ($payment_transaction) {
        //     return error_response(null, 403, 'এই স্টুডেন্টের পেমেন্ট ট্রান্সাকশন রয়েছে, তাই ডিলিট করা যাবে না।');
        // }

        $user = User::find($student->user_id);
        if (!$user) {
            return error_response(null, 404, 'সংশ্লিষ্ট ইউজার খুঁজে পাওয়া যায়নি।');
        }

        DB::beginTransaction();
        try {
            PaymentTransaction::where('student_id', $student->id)->delete();
            Enrole::where('student_id', $student->id)->delete();
    
            TeacherComment::where('student_id', $user->id)->delete();
            Payment::where('user_id', $user->id)->where('paid', '>', 0)->delete(); 
            StudentRegister::where('user_id', $user->id)->delete();
            AdmissionProgressStatus::where('user_id', $user->id)->delete();
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

    /**
     * Enrole student - Promote to next session or department
     * Accepts: department_id, session, marks, fee_type, fee, admission_fee from frontend
     * 
     * @param int $id Student ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enroleStudent($id, Request $request)
    {
        // Validate required fields
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer|in:1,2',
            'session' => 'required|integer',
            'marks' => 'nullable|string',
            'fee_type' => 'required|integer',
            'fee' => 'nullable|numeric',
            'admission_fee' => 'nullable|numeric|min:0',
            'roll_number' => 'nullable|integer',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'ভ্যালিডেশন ব্যর্থ হয়েছে।');
        }

        DB::beginTransaction();
        try {
            // Find student
            $student = Student::find($id);
            if (!$student) {
                return error_response(null, 404, 'স্টুডেন্ট খুঁজে পাওয়া যায়নি।');
            }

            // Get current active enrollment
            $currentEnrole = Enrole::where('student_id', $id)
                ->where('status', 1)
                ->orderByDesc('id')
                ->first();

            if (!$currentEnrole) {
                return error_response(null, 404, 'বর্তমান সক্রিয় নথিভুক্তি খুঁজে পাওয়া যায়নি।');
            }

            // Update current enrollment with marks and mark as completed
            if ($request->filled('marks')) {
                $currentEnrole->marks = $request->input('marks');
            }
            $currentEnrole->status = 2; // Completed
            $currentEnrole->save();

            // Get values from request
            $department_id = $request->input('department_id');
            $session = $request->input('session');
            $fee_type = $request->input('fee_type');
            $fee = $request->input('fee', null);
            $roll_number = $request->input('roll_number', null);

            // Fetch Standard Fees from Settings
            $standard_monthly_fee = 0;
            $setting_admission_fee = 0;

            if ($department_id == Department::Maktab) {
                $standard_monthly_fee = FeeSetting::where('key', 'maktab_monthly_fee')->value('value') ?? 0;
                $setting_admission_fee = FeeSetting::where('key', 'maktab_admission_fee')->value('value') ?? 0;
            } elseif ($department_id == Department::Kitab) {
                $standard_monthly_fee = FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0;
                $setting_admission_fee = FeeSetting::where('key', 'kitab_admission_fee')->value('value') ?? 0;
            }

            // Admission Fee Logic
            // If frontend sends null, use setting fee. Otherwise use input (even if 0).
            if ($request->has('admission_fee') && !is_null($request->input('admission_fee'))) {
                $admission_fee = $request->input('admission_fee');
            } else {
                $admission_fee = $setting_admission_fee;
            }

            // Create new enrollment using service
            $newEnrole = EnrollmentService::createEnrollment([
                'user_id' => $student->user_id,
                'student_id' => $student->id,
                'department_id' => $department_id,
                'session' => $session,
                'roll_number' => $roll_number,
                'fee_type' => $fee_type,
                'fee' => $fee,
                'standard_monthly_fee' => $standard_monthly_fee,
                'admission_fee' => $admission_fee,
                'status' => 1, // Running
            ]);

            // Get active month for response
            $active_month = HijriMonth::where('is_active', true)->first();

            // Get session names for response
            $currentDepartmentId = $currentEnrole->department_id;
            $currentSession = (int) $currentEnrole->session;
            $currentSessionName = null;
            $nextSessionName = null;

            if ($currentDepartmentId === Department::Maktab) {
                $currentSessionName = enum_name(MaktabSession::class, $currentSession);
            } elseif ($currentDepartmentId === Department::Kitab) {
                $currentSessionName = enum_name(KitabSession::class, $currentSession);
            }

            if ($department_id === Department::Maktab) {
                $nextSessionName = enum_name(MaktabSession::class, $session);
            } elseif ($department_id === Department::Kitab) {
                $nextSessionName = enum_name(KitabSession::class, $session);
            }

            DB::commit();

            return success_response([
                'enrollment_id' => $newEnrole->id,
                'department' => enum_name(Department::class, $department_id),
                'session' => $nextSessionName,
                'year' => $active_month->year,
                'fee_type' => enum_name(FeeType::class, $newEnrole->fee_type),
                'fee' => $fee,
                'admission_fee' => $admission_fee,
                'roll_number' => $roll_number,
                'status' => 'running'
            ], 'স্টুডেন্টের পর্যায় সফলভাবে বৃদ্ধি করা হয়েছে।');

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, 'পর্যায় বৃদ্ধি করার সময় একটি ত্রুটি ঘটেছে: ' . $e->getMessage());
        }
    }


}
