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
    public function student($id){
        try {
            $user = User::with(['studentRegister', 'address', 'guardian', 'userFamily', 'admissionProgress', 'answerFiles'])
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
