<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class InterviewStudentListController extends Controller
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
                $q->where('is_passed_age',1);
            });
        }) 
        ->when($status=="unsend",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_age',1)->where('is_interview_scheduled',null)->where('is_registration_complete',1);
            });
        })
        ->when($status=="send",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_interview_scheduled',1)->where('is_passed_interview',null);
            });
        })
        ->when($status=="pass",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_interview',1);
            });
        }) 
        ->when($status=="fail",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_interview',0);
            });
        })
        
        ->with('admissionProgress') 
        ->with('studentRegister')
        ->with('address')
        ->with('guardian')
        ->get();
        return success_response($data);
    }
}
