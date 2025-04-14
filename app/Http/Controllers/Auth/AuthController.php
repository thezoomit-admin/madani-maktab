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

    public function register(RegisterRequest $request){  
        DB::beginTransaction();  
        try { 
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }

            // Create Company
            $company = Company::create([
                'name' => $request->company_name,
                'website' => $request->website,
                'address' => $request->address,
                'logo' => $logoPath,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'founded_date' => $request->founded_date,
                'category_id' => $request->category_id,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]); 

            // Create User
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'password' => Hash::make($request->password),
                'user_type' => 'employee',  
                'profile_image' => $profilePicPath, 
                'role_id' => $request->role_id,
                'company_id' =>  $company->id,
            ]);

            

            // Create User Contact
            UserContact::create([
                'user_id' => $user->id,
                'name' => $request->name ?? $request->user_name,
                'office_phone' => $request->office_phone,
                'personal_phone' => $request->personal_phone,
                'office_email' => $request->office_email,
                'personal_email' => $request->personal_email,
                'emergency_contact_number' => $request->emergency_contact_number,
                'emergency_contact_person' => $request->emergency_contact_person,
            ]);

            // Create User Address
            UserAddress::create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'division_id' => $request->division_id,
                'district_id' => $request->district_id,
                'upazila_id' => $request->upazila_id,
                'address' => $request->address,
            ]);

            // Create Employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => Employee::generateNextEmployeeId(),
                'status' => 1,
            ]);  

            // Create Employee Designation
            EmployeeDesignation::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'designation_id' => $request->designation_id,
                'start_date' => now() 
            ]);  

            DB::commit(); 
            Auth::login($user); 
            return LoginService::createResponse($user); 
        } catch (\Exception $e) { 
            DB::rollBack();  
            return error_response($e->getMessage(), 500);
        }
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
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'string', 'email', 'required_without:reg_id'],
            'reg_id' => ['nullable', 'string', 'required_without:email'],
        ]);

        if ($validator->fails()) {
            return error_response(null, 422, "ভুল ইনপুট: " . $validator->errors()->first());
        }

        $email = $request->input('email');
        $reg_id = $request->input('reg_id');
        $new_password = rand(100000, 999999);

        if ($email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return error_response(null, 404, "এই ইমেইলের কোনও একাউন্ট খুঁজে পাওয়া যায়নি।");
            }
        } else {
            $user = User::where('reg_id', $reg_id)->first();
            if (!$user) {
                return error_response(null, 404, "এই রেজিস্ট্রেশন নম্বরে কোনও একাউন্ট খুঁজে পাওয়া যায়নি।");
            }
        }

        $user->password = Hash::make($new_password);
        $user->save();

        // Send password via SMS
        $messageService = new PhoneMessageService;
        $message = "আপনার নতুন পাসওয়ার্ড: " . $new_password . "। দয়া করে লগইন করার পর পাসওয়ার্ড পরিবর্তন করে নিন।";
        $messageService->sendMessage($user->phone, $message);

        return success_response(null, "নতুন পাসওয়ার্ড সফলভাবে ফোন নম্বরে পাঠানো হয়েছে। অনুগ্রহ করে লগইন করুন।");
    } 
}
