<?php

namespace App\Http\Controllers\Student; 
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\FirstStepRegistrationRequst;
use App\Http\Requests\ExistingStudentRegisterRequest;
use App\Models\Admission;
use App\Models\AdmissionProgressStatus;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\AnswerFile;
use App\Models\UserFamily;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentRegisterController extends Controller
{
    public function firstStep(FirstStepRegistrationRequst $request)
    {    
        DB::beginTransaction();
        try { 
            $dob = Carbon::parse($request->input('dob')); 
            $currentDate = Carbon::now();  
            $ageMonths = $dob->diffInMonths($currentDate);

            if($ageMonths>162){
                return error_response("দুঃখিত! তালিবে ইলমের বয়স নির্ধারিত সীমা অতিক্রম করার কারণে আবেদনটি গ্রহণ করা যাচ্ছে না");
            }

            if ($request->hasFile('profile_image')) { 
                $profileImage = $request->file('profile_image'); 
                $profileImageName = time() . '_' . $profileImage->getClientOriginalName(); 
                $profileImage->move(public_path('uploads/profile_images'), $profileImageName); 
                $profileImageUrl = asset('uploads/profile_images/' . $profileImageName);
            }
            
            if ($request->hasFile('handwriting_image')) { 
                $handwritingImage = $request->file('handwriting_image'); 
                $handwritingImageName = time() . '_' . $handwritingImage->getClientOriginalName(); 
                $handwritingImage->move(public_path('uploads/handwriting_images'), $handwritingImageName); 
                $handwritingImageUrl = asset('uploads/handwriting_images/' . $handwritingImageName);
            } 
           

            $user = User::create([
                'name' => $request->input('name'),
                'phone' => $request->input('contact_number_1'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password', '123456')),
                'profile_image' => $profileImageUrl??null,
                'dob' => $request->input('dob'),
                'age' => $ageMonths,
                'dob_hijri' => $request->input('dob_hijri'),
                'user_type' => 'student', 
            ]);

            if($request->input('department_id')==1){
                $reg_id = StudentRegister::nextMaktabId();
            }else{
                $reg_id = StudentRegister::nextKitabId();
            }   

            $student = StudentRegister::create([
                'user_id' => $user->id,
                'reg_id' => $reg_id,
                'name' => $request->input('name'),
                'father_name' => $request->input('father_name'),
                'department_id' => $request->input('department_id'),
                'bangla_study_status' => $request->input('bangla_study_status'),
                'bangla_others_study' => $request->input('bangla_others_study'),
                'arabi_study_status' => $request->input('arabi_study_status'),
                'arabi_others_study' => $request->input('arabi_others_study'), 
                'handwriting_image' => $handwritingImageUrl??null,
                'previous_education_details' => $request->input('previous_education_details'),
                'hifz_para' => $request->input('hifz_para'),
                'is_other_kitab_study' => $request->input('is_other_kitab_study'),
                'kitab_jamat' => $request->input('kitab_jamat'),
                'is_bangla_handwriting_clear' => $request->input('is_bangla_handwriting_clear'),
                'major_illness_history' => $request->input('major_illness_history'),
                'current_medication_details' => $request->input('current_medication_details'),
            ]);

            
            Guardian::create([
                'user_id'               => $user->id,
                'guardian_name'         => $request->input('guardian_name'),
                'guardian_relation'     => $request->input('guardian_relation'),
                'guardian_occupation_details'   => $request->input('guardian_occupation_details'), 
                'guardian_education'    => $request->input('guardian_education'),                
                'children_count'        => $request->input('children_count'),
                'child_education'       => $request->input('child_education'),
                'contact_number_1'      => $request->input('contact_number_1'),
                'contact_number_2'      => $request->input('contact_number_2'),
                'whatsapp_number'       => $request->input('whatsapp_number'),
                'same_address'          => $request->input('same_address'),
            ]);

            UserAddress::create([
                'user_id'  => $user->id,
                'address_type'      => 'permanent',
                'house_or_state'    => $request->input('house_or_state'),
                'village_or_area'  => $request->input('village_or_area'),
                'post_office'       => $request->input('post_office'),
                'upazila_thana'     => $request->input('upazila_thana'), 
                'district'          => $request->input('district'),
                'division'          => $request->input('division'),
            ]);

            if (!$request->same_address) {
                UserAddress::create([
                    'user_id'  => $user->id,
                    'address_type'      => 'temporary',
                    'house_or_state'    => $request->input('temporary_house_or_state'),
                    'village_or_area'  => $request->input('temporary_village_or_area'),
                    'post_office'       => $request->input('temporary_post_office'),
                    'upazila_thana'     => $request->input('temporary_upazila_thana'), 
                    'district'          => $request->input('temporary_district'),
                    'division'          => $request->input('temporary_division'),
                ]);
            } 

            $passing_status = false; 
            if ($student->department_id == 1) { 
                if ($ageMonths < 78) {
                    $student->note = "বয়স ৬ বছর ৬ মাসের চেয়ে কম।";
                } elseif ($ageMonths > 102) {
                    $student->note = "বয়স ৮ বছর ৬ মাসের বেশি।";
                } else {
                    $passing_status = true;
                } 
                if ($student->bangla_study_status == 1) {
                    $passing_status = false;
                    $student->note = "বাংলা পড়তে পারে না।";
                }
            } else {  
                if ($student->arabi_study_status == 1) {
                    $student->note = "নাজেরা বিভাগ নির্বাচন করেছে।";
                }elseif ($student->arabi_study_status == 2) {
                    if($ageMonths < 78){
                        $student->note = "বয়স ৬ বছর ৬ মাসের চেয়ে কম।";
                    }elseif($ageMonths > 138){
                        $student->note = "বয়স ১১ বছর ৬ মাসের বেশি।";
                    }else{
                        $passing_status = true; 
                    }    
                }elseif($student->arabi_study_status == 3){
                    if ($ageMonths < 78) {
                        $student->note = "বয়স ৬ বছর ৬ মাসের চেয়ে কম।";
                    } elseif ($ageMonths > 162) {
                        $student->note = "বয়স ১৩ বছর ৬ মাসের বেশি।";
                    } else {
                        $passing_status = true;
                    } 
                }elseif($student->arabi_study_status == 4){
                    $student->note = "আংশিক হেফজ বিভাগ নির্বাচন করেছে।";
                }elseif($student->arabi_study_status == 5){
                    $student->note = "আরবি পড়া-লেখার জন্য অন্যান্য বিভাগ নির্বাচন করেছে।";
                }elseif($student->is_other_kitab_study){
                    $student->note = "অন্য কোথাও কিতাব বিভাগে পড়েছে।";
                }

                if ($student->arabi_study_status == 1) {
                    $student->note = "নাজেরা বিভাগ নির্বাচন করেছে।";
                } elseif ($student->arabi_study_status == 2 && ($ageMonths >= 78 && $ageMonths <= 138)) {
                    $passing_status = true;
                } elseif ($student->arabi_study_status == 3 && ($ageMonths >= 78 && $ageMonths <= 162)) {
                    $passing_status = true;
                } elseif ($student->arabi_study_status == 4) {
                    
                } elseif ($student->arabi_study_status == 5) {
                    $student->note = "আরবি পড়া-লেখার জন্য অন্যান্য বিভাগ নির্বাচন করেছে।";
                }
             
                if ($student->is_other_kitab_study) {
                    $passing_status = false;
                    $student->note = "অন্য কোথাও কিতাব বিভাগে পড়েছে।";
                }
            }  
            $student->save();

            AdmissionProgressStatus::create([
                'user_id' => $user->id,
                'is_passed_age' => $passing_status,
            ]);   

            DB::commit();
            return success_response([
                'passing_status' => $passing_status,
                'user_id' => $user->id,
                'reg_id' => $user->studentRegister->reg_id,
                'dep_id' => $student->department_id,
            ], 'অভিনন্দন! আপনার নিবন্ধন সফলভাবে সম্পন্ন হয়েছে।',  201); 
           
        } catch (\Exception $e) {
            DB::rollback();
            return error_response($e->getMessage(), 500);
        }
    }   

    public function lastStep(Request $request){
        DB::beginTransaction();
        try {
            $request->validate([
                'answe_files.*' => 'required|mimes:jpeg,png,jpg,gif,svg',
                'user_id' => 'required|integer',
            ]); 

            if(isset($request->answe_files) && count($request->answe_files)>0){
                foreach ($request->file('answe_files') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/answer_files'), $fileName); 
                    $fileUrl = asset('uploads/answer_files/' . $fileName);
         
                    AnswerFile::create([
                        'user_id'   => $request->input('user_id'),
                        'name'      => $file->getClientOriginalName(),
                        'link'      => $fileUrl,
                        'type'      => $file->getClientOriginalExtension(),
                    ]);
                }
            }  
            $user = User::find($request->input('user_id')); 

            UserFamily::create([
                'user_id' => $request->input('user_id'),
                'deeni_steps' => $request->input('deeni_steps'),
                'follow_porada' => $request->input('follow_porada'),
                'shariah_compliant' => $request->input('shariah_compliant'),
                'motivation' => $request->input('motivation'),
                'info_src' => $request->input('info_src'),
                'first_contact' => $request->input('first_contact'),
                'preparation' => $request->input('preparation'),
                'clean_lang' => $request->input('clean_lang'),
                'future_plan' => $request->input('future_plan'),
                'years_at_inst' => $request->input('years_at_inst'),
                'reason_diff_edu' => $request->input('reason_diff_edu'), 
                'separation_experience' => $request->input('separation_experience'),
                'is_organize_items' => $request->input('is_organize_items'),
                'is_wash_clothes' => $request->input('is_wash_clothes'),
                'is_join_meal' => $request->input('is_join_meal'),
                'is_clean_after_bath' => $request->input('is_clean_after_bath'),
                'health_issue_details' => $request->input('health_issue_details'),
                'is_bath_before_sleep' => $request->input('is_bath_before_sleep') 
            ]);
            
            $department_id = $user->studentRegister->department_id;
            $passing_status = true;
             if($department_id==1 && !$request->input('is_bath_before_sleep')){
                $passing_status = false;
                $user->studentRegister->note = "ঘুমানোর আগে একবার হাম্মাম থেকে ফারেগ হওয়া যথেষ্ট নয়।";
                $user->studentRegister->save();
             }   

             $admission_progress_status = AdmissionProgressStatus::where('user_id', $user->id)->first();
                $admission_progress_status->is_registration_complete = 1;
                $admission_progress_status->is_passed_age = $passing_status;
                $admission_progress_status->save(); 

            DB::commit(); 
            return success_response([
                'passing_status' => $passing_status,
                'user_id' => $user->id,
                'reg_id' => $user->reg_id,
                'dep_id' => $department_id,
            ], 'অভিনন্দন! আপনার নিবন্ধন সফলভাবে সম্পন্ন হয়েছে।',  201);  

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(),500);
        }
    } 
 

}


