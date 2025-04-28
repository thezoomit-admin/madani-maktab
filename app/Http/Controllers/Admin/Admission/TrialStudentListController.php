<?php

namespace App\Http\Controllers\Admin\Admission;

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
                $admission_fee = FeeSetting::where('key', 'maktab_admission_fee')->value('value') ?? 0;
                $session = "প্রথম শ্রেণি";
            } else {
                $monthly_fee = FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0;
                $admission_fee = FeeSetting::where('key', 'kitab_admission_fee')->value('value') ?? 0;
                $session = "প্রথম বর্ষ";
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

            $enrole = Enrole::create([
                'user_id' => $id,
                'student_id' => $student->id,
                'department_id' => $department_id,
                'session' => $session,
                'year' => $request->year,
                'fee_type' => $request->fee_type,
                'fee' => $request->fee ?? null,
                'status' => 1,
            ]);

            $active_month = HijriMonth::where('is_active', true)->first();
            if (!$active_month) {
                DB::rollBack();
                return error_response(null, 400, "কোন অ্যাকটিভ হিজরি মাস নেই।");
            } 

            Payment::create([
                'user_id' => $id,
                'student_id' => $student->id,
                'hijri_month_id' => $active_month->id,
                'reason' => "ভর্তি ফি",
                'year' => $request->session,
                'amount' => $admission_fee,
                'due' => $admission_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            if ($request->fee_type == "আংশিক") {
                $monthly_fee = $request->fee;
            }

            Payment::create([
                'user_id' => $id,
                'student_id' => $student->id,
                'hijri_month_id' => $active_month->id,
                'reason' => "মাসিক ফি",
                'year' => $request->session,
                'fee_type' => $request->fee_type,
                'amount' => $monthly_fee,
                'due' => $monthly_fee,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            DB::commit();
            return success_response(null, "ভর্তি সফলভাবে সম্পন্ন হয়েছে।", 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, "ভর্তি প্রক্রিয়ায় সমস্যা হয়েছে: " . $e->getMessage());
        }
    }

}
