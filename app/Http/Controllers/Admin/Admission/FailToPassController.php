<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FailToPassController extends Controller
{
    public function __invoke($user_id)
    {
        try {
            $user = User::find($user_id);
            if (!$user) {
                return error_response('User not found.', 404);
            }  

            if (!$user->admissionProgress) { 
                AdmissionProgressStatus::create([
                    'user_id' => $user->id,
                    'is_passed_age' => 1,
                ]); 
            }else{ 
                $progress = $user->admissionProgress;
                $registration = $user->studentRegister;

                if($progress->is_passed_trial===0){
                    $progress->is_passed_trial = 1;
                    $registration->note =  $registration->note." চূড়ান্ত পরীক্ষায় মাযেরাত থেকে বিশেষ বিবেচনায় পরবর্তীতে উত্তীর্ণ করে দেওয়া হয়েছে।". Auth::user()->name;
                }elseif($progress->is_passed_interview===0){
                    $progress->is_passed_interview = 1;
                    $registration->note =  $registration->note." প্রাথমিক পরীক্ষায় মাযেরাত থেকে বিশেষ বিবেচনায় পরবর্তীতে উত্তীর্ণ করে দেওয়া হয়েছে।". Auth::user()->name;
                }else{
                    $progress->is_passed_age = 1;
                    $registration->note =  $registration->note." স্বাভাবিক মাযেরাত থেকে বিশেষ বিবেচনায় নিবন্ধিত তালিবে ইলম হিসেবে অন্তর্ভুক্ত করা হয়েছে।". Auth::user()->name;
                }
                $progress->save(); 
                $registration->save();
                $user->updated_by = Auth::user()->id;
                $user->save();
            } 
            return success_response('User has been marked as passed for the age criteria.');
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

}
