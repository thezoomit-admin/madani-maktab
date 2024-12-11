<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentRegisterController extends Controller
{
    public function firstStep(Request $request)
    { 
        $validator = Validator::make($request->all(), [  
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'dob' => 'required|string|max:255',
            'dob_hijri' => 'required|string|max:255',
            'department_id' => 'required|integer',
            'bangla_study_status' => 'required|string|max:255',
            'bangla_others_study' => 'nullable|string|max:255',
            'arabi_study_status' => 'required|string|max:255',
            'arabi_others_study' => 'nullable|string|max:255',
            'study_info_after_seven' => 'required|string|max:255',
            'handwriting_image' => 'nullable|image|max:2048',
            'profile_image' => 'nullable|image|max:2048',
    
            // For Maktab department
            'previous_institution' => 'required_if:department_id,1|string|max:255',
    
            // Kitab-specific fields
            'hifz_para' => 'required_if:department_id,2|nullable|integer',
            'is_other_kitab_study' => 'required_if:department_id,2|integer',
            'kitab_jamat' => 'required_if:is_other_kitab_study,1',
            'is_bangla_handwriting_clear' => 'required_if:department_id,2|integer',
            'kitab_read' => 'required_if:department_id,2|string',

            // Garidan Information 
            'guardian_name' => 'required|string|max:255',
            'guardian_relation' => 'required|string|max:255',
            'guardian_occupation' => 'required|string|max:255',
            'guardian_education' => 'required|string|max:255',
            'guardian_workplace' => 'required|string|max:255',
            'children_count' => 'required|integer|min:0',
            'child_1_education' => 'required|string|max:255',
            'contact_number_1' => 'required|string|max:15',
            'contact_number_2' => 'nullable|string|max:15',
            'whatsapp_number' => 'required|string|max:15',
            'house_or_state' => 'required|string|max:255',
            'post_office' => 'required|string|max:255',
            'upazila' => 'required|integer',
            'district' => 'required|integer',
            'division' => 'required|integer',
            'same_address' => 'nullable|boolean',

            'temporary_house_or_state' => 'required_if:same_address,true|string|max:255',
            'temporary_post_office' => 'required_if:same_address,true|string|max:255',
            'temporary_upazila' => 'required_if:same_address,true|integer',
            'temporary_district' => 'required_if:same_address,true|integer',
            'temporary_division' => 'required_if:same_address,true|integer', 
        ]);  
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        try {
            $is_existing = User::where('phone', $request->phone)->orWhere('email', $request->email)->first(); 
            if(!$is_existing){ 
                $profileImagePath = $request->hasFile('profile_image') 
                    ? $request->file('profile_image')->store('uploads/images', 'public')
                    : null;
        
                $handwritingImagePath = $request->hasFile('handwriting_image') 
                    ? $request->file('handwriting_image')->store('uploads/images', 'public')
                    : null;
        
                // Save data to database
                $user = User::create([
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password', '123456')),
                    'profile_image' => $profileImagePath,
                    'dob' => $request->input('dob'),
                    'dob_hijri' => $request->input('dob_hijri'),
                ]);
        
                Student::create([
                    'user_id' => $user->id,
                    'name' => $request->input('name'),
                    'father_name' => $request->input('father_name'),
                    'department_id' => $request->input('department_id'),
                    'bangla_study_status' => $request->input('bangla_study_status'),
                    'bangla_others_study' => $request->input('bangla_others_study'),
                    'arabi_study_status' => $request->input('arabi_study_status'),
                    'arabi_others_study' => $request->input('arabi_others_study'),
                    'study_info_after_seven' => $request->input('study_info_after_seven'),
                    'handwriting_image' => $handwritingImagePath,
                    'profile_image' => $profileImagePath,
                    'previous_institution' => $request->input('previous_institution'),
                    'hifz_para' => $request->input('hifz_para'),
                    'is_other_kitab_study' => $request->input('is_other_kitab_study'),
                    'kitab_jamat' => $request->input('kitab_jamat'),
                    'is_bangla_handwriting_clear' => $request->input('is_bangla_handwriting_clear'),
                    'kitab_read' => $request->input('kitab_read'),
                ]);
            }else{ 
                $user = $is_existing; 
            } 
            
            $age = Carbon::parse($user->dob)->age;  
            $passing_status = true;
            if ($age >= 8) {
                $passing_status = false;
            }
    
            return response()->json([
                'message' => 'Student and user data stored successfully.',
                'passing_status' => $passing_status,
                'user_id' => $user->id, 
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
