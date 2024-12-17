<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\FirstStepRegistrationRequst;
use App\Models\AdmissionProgressStatus;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFamily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentRegisterController extends Controller
{
    public function firstStep(FirstStepRegistrationRequst $request)
    {    
        DB::beginTransaction();
        try { 
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('uploads/profile_images', 'public'); 
            }   

            $handwritingImagesPaths = [];
            if ($request->hasFile('handwriting_images')) {
                foreach ($request->file('handwriting_images') as $image) {
                    $path = $image->store('uploads/handwriting_images', 'public');
                    $handwritingImagesPaths[] = $path;
                } 
            }

            $dob = Carbon::parse($request->input('dob')); 
            $currentDate = Carbon::now();  
            $ageMonths = $dob->diffInMonths($currentDate);

            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $request->input('contact_number_1'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', '123456')),
                'profile_image' => $profileImagePath??null,
                'dob' => $request->input('dob'),
                'age' => $ageMonths,
                'dob_hijri' => $request->input('dob_hijri'),
                'user_type' => 'student',
                'role_id' => 2,
            ]);

            StudentRegister::create([
                'user_id' => $user->id,
                'reg_id' => "REG-".$user->id,
                'name' => $request->input('name'),
                'father_name' => $request->input('father_name'),
                'department_id' => $request->input('department_id'),
                'bangla_study_status' => $request->input('bangla_study_status'),
                'bangla_others_study' => $request->input('bangla_others_study'),
                'arabi_study_status' => $request->input('arabi_study_status'),
                'arabi_others_study' => $request->input('arabi_others_study'),
                'study_info_after_seven' => $request->input('study_info_after_seven'),
                // 'handwriting_images' => $handwritingImagesPaths, 
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

            if (!$request->same_address) {
                UserAddress::create([
                    'user_id'  => $user->id,
                    'address_type'      => 'temporary',
                    'house_or_state'    => $request->input('temporary_house_or_state'),
                    'post_office'       => $request->input('temporary_post_office'),
                    'upazila'           => $request->input('temporary_upazila'),
                    'district'          => $request->input('temporary_district'),
                    'division'          => $request->input('temporary_division'),
                ]);
            }
            $passing_status = $ageMonths > 53 && $ageMonths < 90; 

            AdmissionProgressStatus::create([
                'user_id' => $user->id,
                'is_passed_age' => $passing_status,
            ]);   

            DB::commit();
            return success_response([
                'passing_status' => $passing_status,
                'user_id' => $user->id,
            ], 'Congratulations! Your registration was successfully completed.',  201); 
           
        } catch (\Exception $e) {
            DB::rollback();
            return error_response($e->getMessage(), 500);
        }
    }


    public function lastStep(Request $request){  
        DB::beginTransaction();
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

            DB::commit();
            return success_response(null, 'Congratulations! Your registration was successfully completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(),500);
        }
    }
}


