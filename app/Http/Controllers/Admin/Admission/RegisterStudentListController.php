<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\Guardian;
use App\Models\StudentRegister;
use App\Models\User;
use App\Models\PreAdmissionTrial;
use App\Models\InterviewSchedule;
use App\Models\UserAddress;
use App\Models\UserFamily;
use Exception;
use Illuminate\Http\Request;

class RegisterStudentListController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = User::where('user_type','student')
        ->whereHas('studentRegister',function($q) use($request){
            $q->where('department_id',$request->department);
        })
        ->whereHas('admissionProgress',function($q) use($request){
            $q->where('is_passed_age',$request->status);
        }) 
        ->with('studentRegister') 
        ->with('admissionProgress')
        ->with('address')
        ->with('guardian')
        ->get();
        return success_response($data);
    }

    public function delete($id){
        try{
            $userStudent = StudentRegister::where('user_id',$id)->first();
            if($userStudent){
                $userStudent->delete();
            } 

            $guardiant = Guardian::where('user_id',$id)->first();
            if($guardiant){
                $guardiant->delete();
            } 

            $address = UserAddress::where('user_id',$id)->first();
            if($address){
                $address->delete();
            } 

            $admissionProcess = AdmissionProgressStatus::where('user_id',$id)->first();
            if($admissionProcess){
                $admissionProcess->delete();
            } 

            $family = UserFamily::where('user_id',$id)->first();
            if($family){
                $family->delete();
            }

            $interview_schedule = InterviewSchedule::where('candidate_id',$id);
            if($interview_schedule){
                $interview_schedule->deete();
            }

            $pre_admission_trial = PreAdmissionTrial::where('candidate_id',$id);
            if($pre_admission_trial){
                $pre_admission_trial->deete();
            }

            $user = User::find($id);
            if($user){
                $user->delete(); 
            } 
            success_response();
        }catch(Exception $e){
            error_response($e->getMessage());
        }
        
    }
}
