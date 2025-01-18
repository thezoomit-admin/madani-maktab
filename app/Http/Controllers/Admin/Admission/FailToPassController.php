<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

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
                $user->admissionProgress->is_passed_age = 1;
                $user->admissionProgress->save();
            } 
            return success_response('User has been marked as passed for the age criteria.');
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

}
