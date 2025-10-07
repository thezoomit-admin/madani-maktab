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
 

}
