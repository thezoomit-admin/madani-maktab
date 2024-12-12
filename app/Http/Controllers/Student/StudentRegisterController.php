<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFamily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentRegisterController extends Controller
{
    public function firstStep(Request $request)
    { 
        $validator = Validator::make($request->all(), [  
            'name' => 'required|string|max:255', 
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
            'upazila' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'same_address' => 'nullable|boolean',

            'temporary_house_or_state' => 'required_if:same_address,false|max:255',
            'temporary_post_office' => 'required_if:same_address,false|max:255',
            'temporary_upazila' => 'required_if:same_address,false|max:255',
            'temporary_district' => 'required_if:same_address,false|max:255',
            'temporary_division' => 'required_if:same_address,false|max:255', 
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
                    'phone' => $request->input('contact_number_1'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password', '123456')),
                    'profile_image' => $profileImagePath,
                    'dob' => $request->input('dob'),
                    'dob_hijri' => $request->input('dob_hijri'),
                    'user_type' => 'student',
                    'role_id' => 2,
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
                    'previous_institution' => $request->input('previous_institution'),
                    'hifz_para' => $request->input('hifz_para'),
                    'is_other_kitab_study' => $request->input('is_other_kitab_study'),
                    'kitab_jamat' => $request->input('kitab_jamat'),
                    'is_bangla_handwriting_clear' => $request->input('is_bangla_handwriting_clear'),
                    'kitab_read' => $request->input('kitab_read'),
                ]);

                Guardian::create([
                    'user_id'               => $user->id,
                    'guardian_name'         => $request->input('guardian_name'),
                    'guardian_relation'     => $request->input('guardian_relation'),
                    'guardian_occupation'   => $request->input('guardian_occupation'),
                    'guardian_education'    => $request->input('guardian_education'),
                    'guardian_workplace'    => $request->input('guardian_workplace'),
                    'children_count'        => $request->input('children_count'),
                    'child_1_education'     => $request->input('child_1_education'),
                    'contact_number_1'      => $request->input('contact_number_1'),
                    'contact_number_2'      => $request->input('contact_number_2'),
                    'whatsapp_number'       => $request->input('whatsapp_number'),
                    'same_address'          => $request->input('same_address'),
                ]);

                UserAddress::create([
                        'user_id'  => $user->id,
                        'address_type'      => 'permanent',
                        'house_or_state'    => $request->input('house_or_state'),
                        'post_office'       => $request->input('post_office'),
                        'upazila'           => $request->input('upazila'),
                        'district'          => $request->input('district'),
                        'division'          => $request->input('division'),
                ]);

                if($request->same_address){
                    UserAddress::create([
                        'user_id'  => $user->id,
                        'address_type'      => 'temporary',
                        'house_or_state'    => $request->input('house_or_state'),
                        'post_office'       => $request->input('post_office'),
                        'upazila'           => $request->input('upazila'),
                        'district'          => $request->input('district'),
                        'division'          => $request->input('division'),
                    ]);
                }
            }else{ 
                $user = $is_existing; 
            } 
            
            $age = Carbon::parse($user->dob)->age;  
            $passing_status = true;
            if ($age >= 8) {
                $passing_status = false;
            }
    
            return response()->json([
                'message' => 'Congratulations! Your registration was successfully completed. We\'re excited to have you with us.',
                'passing_status' => $passing_status,
                'user_id' => $user->id, 
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    } 

    public function lastStep(Request $request){
       // Validate the incoming request
       $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'deeni_steps' => 'required|string|max:255',
            'is_follow_porada' => 'required|boolean',
            'is_shariah_compliant' => 'required|boolean',
            'motivation' => 'required|string|max:255',
            'info_src' => 'required|string|max:255',
            'first_contact' => 'required|date',
            'preparation' => 'nullable|string|max:255',
            'is_clean_lang' => 'required|boolean',
            'future_plan' => 'nullable|string|max:255',
            'years_at_inst' => 'required|integer|min:0',
            'reason_diff_edu' => 'nullable|string|max:2048',
        ]); 
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
 
        try {
            UserFamily::create([
                'user_id' => $request->input('user_id'),
                'deeni_steps' => $request->input('deeni_steps'),
                'is_follow_porada' => $request->boolean('is_follow_porada'),
                'is_shariah_compliant' => $request->boolean('is_shariah_compliant'),
                'motivation' => $request->input('motivation'),
                'info_src' => $request->input('info_src'),
                'first_contact' => $request->input('first_contact'),
                'preparation' => $request->input('preparation'),
                'is_clean_lang' => $request->boolean('is_clean_lang'),
                'future_plan' => $request->input('future_plan'),
                'years_at_inst' => $request->input('years_at_inst'),
                'reason_diff_edu' => $request->input('reason_diff_edu'),
            ]);

            return response()->json([
                'message' => 'Congratulations! Your registration was successfully completed. We\'re excited to have you with us.', 
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while saving the data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


