<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDesignation;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserContact;
use App\Services\PhoneMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {   
        $request->authenticate(); 
        $user = Auth::user();  
        return LoginService::createResponse($user);
    } 
 

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
    
        if ($validator->fails()) {
            return error_response(null, 422, "ভুল ইনপুট: " . $validator->errors()->first());
        }
     
        $user = User::find(Auth::user()->id); 
        if (!Hash::check($request->current_password, $user->password)) {
            return error_response(null, 401, "বর্তমান পাসওয়ার্ড সঠিক নয়।");
        }
    
        $user->password = Hash::make($request->new_password);
        $user->save();
    
        return success_response(null, "পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।");
    }  
    
}
