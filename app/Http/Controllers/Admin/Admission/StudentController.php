<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Enums\FeeType;
use App\Enums\FeeReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\DirectAdmissionRequest;
use App\Models\AdmissionProgressStatus;
use App\Models\Enrole;
use App\Models\FeeSetting;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\UserFamily;
use App\Models\StudentRegister;
use App\Models\Guardian;
use App\Models\UserAddress;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\PaginateTrait;
use App\Traits\HandlesStudentStatus;
use App\Services\RegIdGeneratorService;


class StudentController extends Controller
{
    use PaginateTrait, HandlesStudentStatus; 
    public function index(Request $request)
    {
        $status = $request->status; 
        $department = $request->department;
        $year = $request->year;

        if (!$year) {
            $active_month = HijriMonth::where('is_active', true)->first();
            if ($active_month) {
                $year = $active_month->year;
            }
        } 

        $data = User::where('user_type','student')
            ->when($year, function ($query) use ($year) {
                $range = HijriMonth::getYearRange($year);
                if ($range) {
                    $query->whereBetween('created_at', [$range['start_date'], $range['end_date']]);
                }
            })
            ->whereHas('studentRegister', function($q) use($department) {
                $q->where('department_id', $department);
            });

        // ✅ এখন শুধুমাত্র এক লাইনেই status filter প্রয়োগ
        $data = $this->applyStatusCondition($data, $status);

        $data = $data
            ->with(['admissionProgress', 'studentRegister', 'address', 'guardian']);

        $data = $this->paginateQuery($data, $request);

        // যদি সব student চাও
        if ($status === 'all') {
            $data['data'] = collect($data['data'])->map(function ($student) {
                $student->status = $this->determineStatus($student->admissionProgress);
                return $student;
            });
        }

        return success_response($data);
    }

    


    public function student($id)
    {
        try {
            $user = User::with(['studentRegister', 'address', 'guardian', 'userFamily', 'admissionProgress'])
                ->where('id', $id)
                ->orWhereHas('studentRegister', function ($query) use ($id) {
                    $query->where('reg_id', $id);
                })
                ->first();   
            if ($user && $user->answerFiles) { 
                $user->answerFiles = $user->answerFiles->pluck('link')->toArray();
            } else { 
                $user->answerFiles = [];
            }

            return success_response($user);
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    } 
    

    public function isCompleted(Request $request)
    {
        try { 
            $user_family = UserFamily::where('user_id', $request->user_id)->first(); 
            $student_register = StudentRegister::where('user_id', $request->user_id)->first();
            $dep_id = $student_register ? $student_register->department_id : null;
     
            $is_complete = $user_family ? true : false;
    
            return success_response([
                'is_complete_last_step' => $is_complete,
                'user_id' => $request->user_id,
                'department_id' => $dep_id,
            ]); 
            
        } catch (Exception $e) { 
            return error_response($e->getMessage(), 500);
        }
    } 

    public function first_exam_complete($id){
        $admissionProgress = AdmissionProgressStatus::where('user_id',$id)->first();
        $admissionProgress->is_first_exam_completed = true;
        $admissionProgress->save();
        return success_response(null,"First exam completed");
    }

    public function present_madrasha($id){
        $admissionProgress = AdmissionProgressStatus::where('user_id',$id)->first();
        $admissionProgress->is_present_in_madrasa = true;
        $admissionProgress->save();
        return success_response(null,"Prense in madrasha");
    }

    public function admission(Request $request)
    {
        $id = $request->id; 
        DB::beginTransaction();
        try {  
            $user = User::find($id);  
            $department_id = @$user->studentRegister->department_id;
 
            if ($department_id == 1) {
                $monthly_fee = FeeSetting::where('key', 'maktab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'maktab_new_admission_fee')->value('value') ?? 0;
            } else {
                $monthly_fee = FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'kitab_new_admission_fee')->value('value') ?? 0;
            }

            if (!$user) {
                return error_response(null, 404, "শিক্ষার্থী পাওয়া যায়নি।");
            }

            $is_already_admit = Student::where('user_id', $id)->first();

            if ($is_already_admit) {
                return error_response(null, 409, "এই শিক্ষার্থী ইতোমধ্যে ভর্তি হয়েছে।");
            }

            // Generate reg_id automatically using service
            $regIdGenerator = new RegIdGeneratorService();
            $session = $request->session; // Session number from request
            $reg_id = $regIdGenerator->generate($department_id, $session);

            $user->reg_id = $reg_id;
            $user->save();

            $student = Student::create([
                'user_id' => $id,
                'reg_id' => $reg_id,
                'jamaat' => $request->jamaat,
                'average_marks' => 0,
                'status' => 1
            ]); 
 
            if(isset($request->admission_fee)){
                $admission_fee = $request->admission_fee;
            }

            $fee_type = $request->fee_type;
            $regular_monthly_fee = $monthly_fee;
            if ($fee_type == FeeType::Half) {
                $monthly_fee = $request->fee;
                 $regular_monthly_fee = $request->fee;
            }elseif($fee_type == FeeType::Guest){
                $monthly_fee = 0;
                $regular_monthly_fee = 0;
            }elseif($fee_type == FeeType::HalfButThisMonthGeneral){
                $fee_type = FeeType::Half;
                $regular_monthly_fee = $request->fee;
            }elseif($fee_type == FeeType::GuestButThisMonthGeneral){
                $fee_type = FeeType::Guest;
                $regular_monthly_fee = 0;
            } 

            $active_month = HijriMonth::where('is_active', true)->first();
            if (!$active_month) {
                DB::rollBack();
                return error_response(null, 400, "কোন অ্যাকটিভ হিজরি মাস নেই।");
            } 

            $enrole = Enrole::create([
                'user_id' => $id,
                'roll_number' => $request->roll_number,
                'student_id' => $student->id,
                'department_id' => $department_id,
                'session' => $request->session,
                'year' => $active_month->year,
                'fee_type' =>  $fee_type,
                'fee' => $request->fee ?? null,
                'status' => 1,
            ]); 

            Payment::create([
                'user_id' => $id,
                'student_id' => $student->id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $active_month->id,
                'reason' => 1,
                'year' => $active_month->year,
                'amount' => $admission_fee,
                'due' => $admission_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]); 

            Payment::create([
                'user_id' => $id,
                'student_id' => $student->id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $active_month->id,
                'reason' => 2,
                'year' => $active_month->year,
                'fee_type' => $request->fee_type,
                'amount' => $monthly_fee,
                'due' => $monthly_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            $months = HijriMonth::where('id',">",$active_month->id)->get();
            foreach($months as $month){
                Payment::create([
                    'user_id' => $id,
                    'student_id' => $student->id,
                    'enrole_id' => $enrole->id,
                    'hijri_month_id' => $month->id,
                    'reason' => 2,
                    'year' => $enrole->year,
                    'fee_type' => $enrole->fee_type,
                    'amount' => $regular_monthly_fee,
                    'due' => $regular_monthly_fee,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }

            $user->admissionProgress->is_admission_completed=1;
            $user->admissionProgress->save();
            DB::commit();
            return success_response(null, "ভর্তি সফলভাবে সম্পন্ন হয়েছে।", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, "ভর্তি প্রক্রিয়ায় সমস্যা হয়েছে: " . $e->getMessage());
        }
    }

    public function directAdmission(DirectAdmissionRequest $request)
    {
 
        DB::beginTransaction();
        try {  
            $dob = Carbon::parse($request->input('dob'));
            $currentDate = Carbon::now();
            $ageMonths = $dob->diffInMonths($currentDate);
 
            $profileImageUrl = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImageName = time() . '_' . $profileImage->getClientOriginalName();
                $profileImage->move(public_path('uploads/profile_images'), $profileImageName);
                $profileImageUrl = asset('uploads/profile_images/' . $profileImageName);
            }

            // Create User 
            $reg_id = $request->reg_id;
            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', '123456')),
                'profile_image' => $profileImageUrl,
                'dob' => $request->input('dob'),
                'age' => $ageMonths,
                'dob_hijri' => $request->input('dob_hijri'), 
                'user_type' => 'student',
                'reg_id' => $reg_id,
            ]);
 
            // $guardian = Guardian::where('user_id', $user->id)->first();
            // $guardianData = [
            //     'guardian_name' => $request->input('guardian_name'),
            //     'guardian_relation' => $request->input('guardian_relation'),
            //     'guardian_occupation_details' => $request->input('guardian_occupation_details'),
            //     'guardian_education' => $request->input('guardian_education'),
            //     'children_count' => $request->input('children_count'),
            //     'contact_number_1' => $request->input('contact_number_1'),
            //     'contact_number_2' => $request->input('contact_number_2'),
            //     'whatsapp_number' => $request->input('whatsapp_number'),
            //     'same_address' => $request->input('same_address'),
            //     'user_id' => $user->id,
            // ];

         
            // if ($request->has('child_education')) {
            //     $childEducation = $request->input('child_education');
            //     if (is_array($childEducation)) {
            //         $guardianData['child_education'] = $childEducation;
            //     } elseif (is_string($childEducation) && !empty($childEducation)) { 
            //         $guardianData['child_education'] = [$childEducation];
            //     } else {
            //         $guardianData['child_education'] = null;
            //     }
            // } else {
            //     $guardianData['child_education'] = null;
            // }

            // Guardian::create($guardianData);
            
            // $permanentAddressData = [
            //     'user_id' => $user->id,
            //     'address_type' => 'permanent',
            //     'house_or_state' => $request->input('house_or_state'),
            //     'village_or_area' => $request->input('village_or_area'),
            //     'post_office' => $request->input('post_office'),
            //     'upazila_thana' => $request->input('upazila_thana'),
            //     'district' => $request->input('district'),
            //     'division' => $request->input('division'),
            // ];
            // UserAddress::create($permanentAddressData);
            
            // if (!$request->same_address) {
            //     $temporaryAddressData = [
            //         'user_id' => $user->id,
            //         'address_type' => 'temporary',
            //         'house_or_state' => $request->input('temporary_house_or_state'),
            //         'village_or_area' => $request->input('temporary_village_or_area'),
            //         'post_office' => $request->input('temporary_post_office'),
            //         'upazila_thana' => $request->input('temporary_upazila_thana'),
            //         'district' => $request->input('temporary_district'),
            //         'division' => $request->input('temporary_division'),
            //     ];
            //     UserAddress::create($temporaryAddressData);
            // }

            // Get fee settings based on department 
            $department_id = $request->department_id;
            if ($department_id == 1) {
                $monthly_fee = FeeSetting::where('key', 'maktab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'maktab_new_admission_fee')->value('value') ?? 0;
            } else {
                $monthly_fee = FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'kitab_new_admission_fee')->value('value') ?? 0;
            }

            if (isset($request->admission_fee)) {
                $admission_fee = $request->admission_fee;
            }

            // Create Student
            $student = Student::create([
                'user_id' => $user->id,
                'reg_id' => $reg_id,
                'jamaat' => $request->input('jamaat'),
                'average_marks' => $request->input('average_marks') ?? 0,
                'status' => $request->input('status') ?? 1
            ]);
 
            $fee_type = $request->fee_type;
            $regular_monthly_fee = $monthly_fee;
            if ($fee_type == FeeType::Half) {
                $monthly_fee = $request->fee;
                $regular_monthly_fee = $request->fee;
            } elseif ($fee_type == FeeType::Guest) {
                $monthly_fee = 0;
                $regular_monthly_fee = 0;
            } elseif ($fee_type == FeeType::HalfButThisMonthGeneral) {
                $fee_type = FeeType::Half;
                $regular_monthly_fee = $request->fee;
            } elseif ($fee_type == FeeType::GuestButThisMonthGeneral) {
                $fee_type = FeeType::Guest;
                $regular_monthly_fee = 0;
            }

            // Get active month
            $active_month = HijriMonth::where('is_active', true)->first();
            if (!$active_month) {
                DB::rollBack();
                return error_response(null, 400, "কোন অ্যাকটিভ হিজরি মাস নেই।");
            }

            // Create Enrole
            $enrole = Enrole::create([
                'user_id' => $user->id,
                'roll_number' => $request->roll_number,
                'student_id' => $student->id,
                'department_id' => $department_id,
                'session' => $request->session,
                'year' => $active_month->year,
                'fee_type' => $fee_type,
                'fee' => $request->fee ?? null,
                'status' => 1,
            ]);

            // Create Admission Fee Payment
            Payment::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $active_month->id,
                'reason' => FeeReason::ADMISSION,
                'year' => $active_month->year,
                'amount' => $admission_fee,
                'due' => $admission_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // Create Monthly Fee Payment for current month
            Payment::create([
                'user_id' => $user->id,
                'student_id' => $student->id,
                'enrole_id' => $enrole->id,
                'hijri_month_id' => $active_month->id,
                'reason' => FeeReason::MONTHLY,
                'year' => $active_month->year,
                'fee_type' => $request->fee_type,
                'amount' => $monthly_fee,
                'due' => $monthly_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // Create Monthly Fee Payments for future months
            $months = HijriMonth::where('id', ">", $active_month->id)->get();
            foreach ($months as $month) {
                Payment::create([
                    'user_id' => $user->id,
                    'student_id' => $student->id,
                    'enrole_id' => $enrole->id,
                    'hijri_month_id' => $month->id,
                    'reason' => 2,
                    'year' => $enrole->year,
                    'fee_type' => $enrole->fee_type,
                    'amount' => $regular_monthly_fee,
                    'due' => $regular_monthly_fee,
                    'created_by' => Auth::user()->id,
                    'updated_by' => Auth::user()->id,
                ]);
            }

            DB::commit();
            return success_response($user, "সরাসরি ভর্তি সফলভাবে সম্পন্ন হয়েছে।", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, "সরাসরি ভর্তি প্রক্রিয়ায় সমস্যা হয়েছে: " . $e->getMessage());
        }
    }

    

}