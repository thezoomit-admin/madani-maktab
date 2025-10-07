<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Enums\FeeType;
use App\Http\Controllers\Controller;
use App\Models\Enrole;
use App\Models\FeeSetting;
use App\Models\HijriMonth;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrialStudentListController extends Controller
{
    public function __invoke(Request $request)
    {
        $status = $request->status; 
        $data = User::where('user_type','student')
        ->whereHas('studentRegister',function($q) use($request){
            $q->where('department_id',$request->department);
        })
        ->when($status=="all",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_interview',1);
            });
        }) 
        ->when($status=="unrequested",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_interview',1)->where('is_invited_for_trial',null);
            });
        })
        ->when($status=="requested",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_invited_for_trial',1)->where('is_passed_trial',null);
            });
        }) 
        ->when($status=="pass",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_trial',1);
            });
        }) 
        ->when($status=="fail",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_trial',0);
            });
        }) 
        ->with('admissionProgress') 
        ->with('studentRegister') 
        ->with('address')
        ->with('guardian')
        ->get();
        return success_response($data);
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

            $user->reg_id = $request->reg_id;
            $user->save();

            $student = Student::create([
                'user_id' => $id,
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

            DB::commit();
            return success_response(null, "ভর্তি সফলভাবে সম্পন্ন হয়েছে।", 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, "ভর্তি প্রক্রিয়ায় সমস্যা হয়েছে: " . $e->getMessage());
        }
    }

}
