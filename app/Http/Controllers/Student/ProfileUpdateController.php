<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AnswerFile;
use App\Models\Guardian;
use App\Models\StudentRegister;
use App\Models\Student;
use App\Models\Enrole;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserFamily;
use App\Traits\HandlesImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileUpdateController extends Controller
{
    use HandlesImageUpload;  
    public function updateBasic(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'roll_number' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048',
            'reg_id' => 'nullable|string',
            'dob' => 'nullable|date',
            'dob_hijri' => 'nullable|string|max:30',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'jamaat' => 'nullable|string',
            'gender' => 'nullable|in:male,female,others',
            'status' => 'nullable|integer',
            'position' => 'nullable|string',
        ], [
            'name.required' => 'নাম আবশ্যক।',
            'name.max' => 'নাম সর্বোচ্চ ২৫৫ অক্ষর হতে পারবে।',
            'profile_image.image' => 'প্রোফাইল ছবি অবশ্যই ইমেজ ফাইল হতে হবে।',
            'profile_image.max' => 'প্রোফাইল ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট।',
            'blood_group.in' => 'রক্তের গ্রুপ সঠিক নয়।',
            'gender.in' => 'লিঙ্গ সঠিক নয়।',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        } 

         if ($request->hasFile('profile_image')) { 
            $profileImageUrl = $this->uploadImage($request, 'profile_image', 'uploads/profile_images');
            if ($profileImageUrl) {
                $user->profile_image = $profileImageUrl;
                $user->save();
            }
        }


        $user->fill($request->only([
            'reg_id','name', 'dob', 'dob_hijri', 'blood_group', 'gender'
        ]))->save();  

        $student = Student::where('user_id', $id)->first();
        if ($student) {
            $student->fill($request->only([
                'reg_id', 'jamaat', 'status','position'
            ]))->save();

            $enrole = Enrole::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            if ($enrole) {
                $enrole->roll_number = $request->roll_number;
                $enrole->save();
            }
        }

        $student_register = StudentRegister::where('user_id', $id)->first();
        if ($student_register) {
            $student_register->name = $user->name;
            $student_register->save();
        }
        return success_response(null, 'মৌলিক তথ্য আপডেট হয়েছে।');
    }

    /* ---------- EDUCATION ---------- */
    public function updateEducation(Request $request, $id)
    {
        $reg  = StudentRegister::firstOrNew(['user_id'=>$id]); 

        $validator = Validator::make($request->all(), [
            'department_id'                => 'required|integer',
            'bangla_study_status'          => 'nullable|string|max:255',
            'bangla_others_study'          => 'nullable|string|max:255',
            'arabi_study_status'           => 'nullable|string|max:255',
            'arabi_others_study'           => 'nullable|string|max:255',
            'previous_education_details'   => 'nullable|string',
            'hifz_para'                    => 'nullable|string|max:100',
            'is_other_kitab_study'         => 'nullable|boolean',
            'kitab_jamat'                  => 'nullable|string|max:100',
            'is_bangla_handwriting_clear'  => 'nullable|boolean',
            'note'                         => 'nullable|string',
            'handwriting_image'            => 'nullable|image|max:2048',
        ], [
            'department_id.required' => 'বিভাগ নির্বাচন আবশ্যক।',
            'department_id.integer' => 'বিভাগ সঠিক নয়।',
            'handwriting_image.image' => 'হাতের লেখার ছবি অবশ্যই ইমেজ ফাইল হতে হবে।',
            'handwriting_image.max' => 'ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট।',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }


         if ($request->hasFile('handwriting_image')) { 
            $handwritingImageUrl = $this->uploadImage($request, 'handwriting_image', 'uploads/handwriting_images');
            if ($handwritingImageUrl) {
                $reg->handwriting_image = $handwritingImageUrl;
            }
        }  
 
        $reg->fill($request->except('handwriting_image'))->save();

        return success_response(null, 'শিক্ষাগত তথ্য আপডেট হয়েছে।');
        
    }

    /* ---------- ADDRESS ---------- */
   public function updateAddress(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [ 
            'house_or_state'      => 'required|string|max:255',
            'village_or_area'     => 'required|string|max:255',
            'post_office'         => 'required|string|max:255',
            'upazila_thana'       => 'required|string|max:255',
            'district'            => 'required|string|max:255',
            'division'            => 'required|string|max:255',

            'same_address'                 => 'nullable|boolean',
            'temporary_house_or_state'    => 'required_if:same_address,false|string|max:255',
            'temporary_village_or_area'   => 'required_if:same_address,false|string|max:255',
            'temporary_post_office'       => 'required_if:same_address,false|string|max:255',
            'temporary_upazila_thana'     => 'required_if:same_address,false|string|max:255',
            'temporary_district'          => 'required_if:same_address,false|string|max:255',
            'temporary_division'          => 'required_if:same_address,false|string|max:255',
        ], [
            'house_or_state.required' => 'বাড়ি/রাজ্য আবশ্যক।',
            'village_or_area.required' => 'গ্রাম/এলাকা আবশ্যক।',
            'post_office.required' => 'ডাকঘর আবশ্যক।',
            'upazila_thana.required' => 'উপজেলা/থানা আবশ্যক।',
            'district.required' => 'জেলা আবশ্যক।',
            'division.required' => 'বিভাগ আবশ্যক।',
            'temporary_house_or_state.required_if' => 'অস্থায়ী ঠিকানার বাড়ি/রাজ্য আবশ্যক।',
            'temporary_village_or_area.required_if' => 'অস্থায়ী ঠিকানার গ্রাম/এলাকা আবশ্যক।',
            'temporary_post_office.required_if' => 'অস্থায়ী ঠিকানার ডাকঘর আবশ্যক।',
            'temporary_upazila_thana.required_if' => 'অস্থায়ী ঠিকানার উপজেলা/থানা আবশ্যক।',
            'temporary_district.required_if' => 'অস্থায়ী ঠিকানার জেলা আবশ্যক।',
            'temporary_division.required_if' => 'অস্থায়ী ঠিকানার বিভাগ আবশ্যক।',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }
 
        UserAddress::updateOrCreate(
            ['user_id' => $user->id, 'address_type' => 'permanent'],
            [
                'house_or_state'    => $request->house_or_state,
                'village_or_area'   => $request->village_or_area,
                'post_office'       => $request->post_office,
                'upazila_thana'     => $request->upazila_thana,
                'district'          => $request->district,
                'division'          => $request->division,
            ]
        );
 
        if (!$request->same_address) {
            UserAddress::updateOrCreate(
                ['user_id' => $user->id, 'address_type' => 'temporary'],
                [
                    'house_or_state'    => $request->temporary_house_or_state,
                    'village_or_area'   => $request->temporary_village_or_area,
                    'post_office'       => $request->temporary_post_office,
                    'upazila_thana'     => $request->temporary_upazila_thana,
                    'district'          => $request->temporary_district,
                    'division'          => $request->temporary_division,
                ]
            );
        } else { 
            UserAddress::where('user_id', $user->id)->where('address_type', 'temporary')->delete();
        }

        $guardian = Guardian::where('user_id',$id)->first();
        if($guardian){
            $guardian->same_address =  $request->same_address;
        }

        return success_response(null, 'ঠিকানা সফলভাবে আপডেট হয়েছে।', 200);
    }


    /* ---------- GUARDIAN ---------- */
    public function updateGuardian(Request $request, $id)
    {
        $guardian = Guardian::firstOrNew(['user_id'=>$id]);
        $validator = Validator::make($request->all(), [
            'guardian_name'              => 'required|string|max:255',
            'email'                      => 'nullable|email',
            'guardian_relation'          => 'required|string|max:100',
            'guardian_occupation_details'=> 'required|string',
            'guardian_education'         => 'required|string',
            'children_count'             => 'nullable|integer',
            'child_education'            => 'nullable|array',
            'contact_number_1'           => 'nullable|string|max:15',
            'contact_number_2'           => 'nullable|string|max:15',
            'whatsapp_number'            => 'nullable|string|max:15',
            'father_name'                => 'nullable|string|max:255',
        ], [
            'guardian_name.required' => 'অভিভাবকের নাম আবশ্যক।',
            'guardian_name.max' => 'অভিভাবকের নাম সর্বোচ্চ ২৫৫ অক্ষর।',
            'email.email' => 'সঠিক ইমেইল ঠিকানা দিন।',
            'guardian_relation.required' => 'সম্পর্ক আবশ্যক।',
            'guardian_relation.max' => 'সম্পর্ক সর্বোচ্চ ১০০ অক্ষর।',
            'guardian_occupation_details.required' => 'পেশার বিবরণ আবশ্যক।',
            'guardian_education.required' => 'শিক্ষাগত যোগ্যতা আবশ্যক।',
            'contact_number_1.max' => 'যোগাযোগ নম্বর ১ সর্বোচ্চ ১৫ অঙ্ক।',
            'contact_number_2.max' => 'যোগাযোগ নম্বর ২ সর্বোচ্চ ১৫ অঙ্ক।',
            'whatsapp_number.max' => 'হোয়াটসঅ্যাপ নম্বর সর্বোচ্চ ১৫ অঙ্ক।',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        $user = User::find($id);
        if ($user && $request->has('email')) {
            $user->email = $request->email;
            $user->save();
        }

        $guardian->fill($request->except(['father_name','child_education']));
        if ($request->has('child_education')) {
            $guardian->child_education = json_encode($request->child_education);
        }
        $guardian->save();

        // father_name → student_registers
        if ($request->filled('father_name')) {
            $student_register = StudentRegister::where('user_id', $id)->first();
            if($student_register){
                $student_register->father_name = $request->father_name;
                $student_register->save();
            } 
        }

        return success_response(null, 'অভিভাবকের তথ্য আপডেট হয়েছে।');
    }

    /* ---------- FAMILY ---------- */
    public function updateFamily(Request $request, $id)
    {
        $family = UserFamily::firstOrNew(['user_id'=>$id]);
        $validator = Validator::make($request->all(), [
            'deeni_steps'          => 'sometimes|string',
            'follow_porada'        => 'sometimes|string',
            'shariah_compliant'    => 'sometimes|string',
            'motivation'           => 'sometimes|string',
            'info_src'             => 'sometimes|string',
            'first_contact'        => 'sometimes|string',
            'preparation'          => 'sometimes|string',
            'clean_lang'           => 'sometimes|string',
            'future_plan'          => 'sometimes|string',
            'years_at_inst'        => 'sometimes|integer',
            'reason_diff_edu'      => 'sometimes|string',
            'separation_experience'=> 'sometimes|string',
            'is_organize_items'    => 'sometimes|boolean',
            'is_wash_clothes'      => 'sometimes|boolean',
            'is_join_meal'         => 'sometimes|boolean',
            'is_clean_after_bath'  => 'sometimes|boolean',
            'health_issue_details' => 'sometimes|string',
            'is_bath_before_sleep' => 'sometimes|boolean',
        ], [
            'years_at_inst.integer' => 'ইনস্টিটিউশনে থাকার বছর পূর্ণ সংখ্যা হতে হবে।',
            'is_organize_items.boolean' => 'অসংগত মান।',
            'is_wash_clothes.boolean' => 'অসংগত মান।',
            'is_join_meal.boolean' => 'অসংগত মান।',
            'is_clean_after_bath.boolean' => 'অসংগত মান।',
            'is_bath_before_sleep.boolean' => 'অসংগত মান।',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        $family->fill($request->all())->save();

        return success_response(null, 'পারিবারিক তথ্য আপডেট হয়েছে।');
    }  

    public function storeAnswerFile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'file' => 'required',
        ], [
            'file.required' => 'ফাইল আবশ্যক।',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        $fileUrl = $this->uploadImage($request, 'file', 'uploads/answer_files');
        
        if ($fileUrl) {
            AnswerFile::create([
                'user_id' => $id,
                'name' => $request->name ?? $request->file('file')->getClientOriginalName(),
                'link' => $fileUrl,
                'type' => $request->file('file')->getClientMimeType(),
            ]);
        }
        return success_response(null, 201, 'ফাইল সেভ হয়েছে।');
    }


    public function destroyAnswerFile($fileId)
    {
        $file = AnswerFile::find($fileId);

        if(!$file){
            return error_response(null, 404, 'ফাইল পাওয়া যায়নি।');
        }  
        // $relativePath = str_replace(asset('storage/') . '/', '', $file->link);
        // Storage::disk('public')->delete($relativePath);
        $file->delete();
        return success_response(null, 200, 'ফাইল ডিলিট হয়েছে।');
    }

}
