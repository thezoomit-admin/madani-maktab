<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFamily;
use Exception;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function student($id){
        try {
            $user = User::with(['studentRegister', 'address', 'guardian','userFamily'])
                ->where('id', $id)
                ->orWhereHas('studentRegister', function ($query) use ($id) {
                    $query->where('reg_id', $id);
                })
                ->first(); 
            return success_response($user);
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
        
    }

    public function isCompleted(Request $request)
    {
        try {
            $user_family = UserFamily::where('user_id', $request->user_id)->first(); // Corrected query
            $is_complete = false;  
            
            if ($user_family) {
                $is_complete = true;
            }  

            return success_response([
                'is_complete_last_step' => $is_complete,
                'user_id' => $request->user_id,
            ]);
        } catch (Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

}
