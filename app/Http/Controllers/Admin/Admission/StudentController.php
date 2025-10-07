<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFamily;
use App\Models\StudentRegister;
use Exception;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request){
        $status = $request->status; 
        $department = $request->department;
        $data = User::where('user_type','student')
        ->whereHas('studentRegister',function($q) use($department){
            $q->where('department_id',$department);
        })

        //স্বাভবিক মাযেরাত
        ->when($status=="normal_failed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_age',false);
            });
        }) 

        //বার্তা পাঠানো হয়নি
        ->when($status=="message_not_sent",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_age',true)->where('is_send_step_2_link',null);
            });
        }) 

        //বার্তা পাঠানো হয়েছে
        ->when($status=="message_sent",function($q){
            $q->whereHas('admissionProgress',function($q){
                 $q->where('is_send_step_2_link',true)->where('is_registration_complete',null);
            });
        }) 

        //২য় ধাপ সম্পন্ন
        ->when($status=="second_step_completed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_registration_complete',true)->where('is_interview_scheduled',null);
            });
        }) 

        //পরীক্ষার বার্তা পাঠানো হয়েছে
        ->when($status=="exam_message_sent",function($q){
            $q->whereHas('admissionProgress',function($q){
                 $q->where('is_interview_scheduled',true)->where('is_first_exam_completed',null); 
            });
        }) 

        //১ম পরীক্ষা সম্পন্ন
        ->when($status=="first_exam_completed",function($q){
            $q->whereHas('admissionProgress',function($q){
               $q->where('is_first_exam_completed',true)->where('is_passed_interview',null); 
            });
        }) 

        //ফুরসত
        ->when($status=="passed",function($q){
            $q->whereHas('admissionProgress',function($q){
                 $q->where('is_passed_interview',true)->where('is_invited_for_trial',null);
            });
        })

        //মাযেরাত
        ->when($status=="failed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_interview',false);
            });
        }) 

        //আমন্ত্রিত
        ->when($status=="invited",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_invited_for_trial',true)->where('is_present_in_madrasa',null);
            });
        }) 

        //মাদরাসায় উপস্থিত
        ->when($status=="present_in_madrasa",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_present_in_madrasa',true)->where('is_passed_trial',null);
            });
        })

        //পর্যবেক্ষণে উত্তীর্ণ
        ->when($status=="observation_passed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_trial',true)->where('is_admission_completed',null);
            });
        }) 

        //পর্যবেক্ষণে মাযেরাত
        ->when($status=="observation_failed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_passed_trial',false);
            });
        }) 

        //দাখেলা সম্পন্ন
        ->when($status=="admission_completed",function($q){
            $q->whereHas('admissionProgress',function($q){
                $q->where('is_admission_completed',true);
            });
        })  
        ->with('admissionProgress') 
        ->with('studentRegister')
        ->with('address')
        ->with('guardian')
        ->get();
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

}
