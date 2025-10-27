<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\PhoneMessageService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{ 
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 401);
        }

        $last11Digits = substr($request->phone, -11);
        $user = User::whereRaw('SUBSTR(phone, -11) = ?', [$last11Digits])->first();

        if (!$user) {
            return error_response('User with this phone number not found.', 404);
        } 

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5); 
        $user->save();
       
        $messageService = new PhoneMessageService;
        $message = "Your OTP for password reset is: " . $otp;
        $messageService->sendMessage($user->phone, $message);
        return success_response(['phone' => $user->phone], 'OTP sent successfully to your phone number.');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|digits:11',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 401);
        }

        $last11Digits = substr($request->phone, -11);
        $user = User::whereRaw('SUBSTR(phone, -11) = ?', [$last11Digits])->first();

        if (!$user) {
            return error_response('User with this phone number not found.', 404);
        }

        // OTP is valid, clear it and allow password reset
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return success_response(['phone' => $user->phone], 'OTP verified successfully. You can now reset your password.');
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|digits:11',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        $last11Digits = substr($request->phone, -11);
        $user = User::whereRaw('SUBSTR(phone, -11) = ?', [$last11Digits])->first();

        if (!$user) {
            return error_response('User with this phone number not found.', 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return success_response([], 'Password has been reset successfully.');
    }
}
