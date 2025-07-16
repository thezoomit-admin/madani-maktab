<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileUpdateController extends Controller
{  
    public function updateBasic(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'phone'         => 'sometimes|string|max:15',
            'email'         => 'sometimes|email|max:255',
            'profile_image' => 'sometimes|image|max:2048',
            'dob'           => 'sometimes|date',
            'dob_hijri'     => 'sometimes|string|max:30',
            'blood_group'   => 'sometimes|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'gender'        => 'sometimes|in:male,female,others',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }
         

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $path;
        }
 
        if ($request->hasFile('profile_image')) {
            $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $profilePicPath;
        } 


        $user->fill($request->only([
            'name','phone','email','dob','dob_hijri','blood_group','gender'
        ]))->save(); 
        return success_response(null,'মৌলিক তথ্য আপডেট হয়েছে।');
    }

    /* ---------- EDUCATION ---------- */
    public function updateEducation(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $reg  = StudentRegister::firstOrNew(['user_id'=>$id]);

        $request->validate([
            'department_id'                => 'sometimes|integer',
            'bangla_study_status'          => 'sometimes|string|max:255',
            'bangla_others_study'          => 'sometimes|string|max:255',
            'arabi_study_status'           => 'sometimes|string|max:255',
            'arabi_others_study'           => 'sometimes|string|max:255',
            'previous_education_details'   => 'sometimes|string',
            'hifz_para'                    => 'sometimes|string|max:100',
            'is_other_kitab_study'         => 'sometimes|boolean',
            'kitab_jamat'                  => 'sometimes|string|max:100',
            'is_bangla_handwriting_clear'  => 'sometimes|boolean',
            'note'                         => 'sometimes|string',
            'handwriting_image'            => 'sometimes|image|max:2048',
        ]);

        if ($request->hasFile('handwriting_image')) {
            $reg->handwriting_image = $request->file('handwriting_image')
                                              ->store('handwriting_images', 'public');
        }

        $reg->fill($request->except('handwriting_image'))->save();

        return success_response(null, 200, 'শিক্ষাগত তথ্য আপডেট হয়েছে।');
    }

    /* ---------- ADDRESS ---------- */
    public function updateAddress(Request $request, $id)
    {
        // address_type না দিলে permanent ধরা হল
        $type    = $request->get('address_type', 'permanent');
        $address = UserAddress::firstOrNew([
            'user_id'      => $id,
            'address_type' => $type,
        ]);

        $request->validate([
            'address_type'     => 'sometimes|in:permanent,temporary',
            'house_or_state'   => 'sometimes|string|max:255',
            'village_or_area'  => 'sometimes|string|max:255',
            'post_office'      => 'sometimes|string|max:255',
            'upazila_thana'    => 'sometimes|string|max:255',
            'district'         => 'sometimes|string|max:255',
            'division'         => 'sometimes|string|max:255',
        ]);

        $address->fill($request->only([
            'address_type','house_or_state','village_or_area','post_office',
            'upazila_thana','district','division'
        ]))->save();

        return success_response(null, 200, 'ঠিকানা আপডেট হয়েছে।');
    }

    /* ---------- GUARDIAN ---------- */
    public function updateGuardian(Request $request, $id)
    {
        $guardian = Guardian::firstOrNew(['user_id'=>$id]);

        $request->validate([
            'guardian_name'              => 'sometimes|string|max:255',
            'guardian_relation'          => 'sometimes|string|max:100',
            'guardian_occupation_details'=> 'sometimes|string',
            'guardian_education'         => 'sometimes|string',
            'children_count'             => 'sometimes|integer',
            'child_education'            => 'sometimes|array',
            'contact_number_1'           => 'sometimes|string|max:15',
            'contact_number_2'           => 'sometimes|string|max:15',
            'whatsapp_number'            => 'sometimes|string|max:15',
            'same_address'               => 'sometimes|boolean',
            'father_name'                => 'sometimes|string|max:255',
        ]);

        $guardian->fill($request->except(['father_name','child_education']));
        if ($request->has('child_education')) {
            $guardian->child_education = json_encode($request->child_education);
        }
        $guardian->save();

        // father_name → student_registers
        if ($request->filled('father_name')) {
            StudentRegister::updateOrCreate(
                ['user_id'=>$id],
                ['father_name'=>$request->father_name]
            );
        }

        return success_response(null, 200, 'অভিভাবকের তথ্য আপডেট হয়েছে।');
    }

    /* ---------- FAMILY ---------- */
    public function updateFamily(Request $request, $id)
    {
        $family = UserFamily::firstOrNew(['user_id'=>$id]);

        $request->validate([
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
        ]);

        $family->fill($request->all())->save();

        return success_response(null, 200, 'পারিবারিক তথ্য আপডেট হয়েছে।');
    }

    /* ---------- ANSWER FILES ---------- */
    public function storeAnswerFile(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimetypes:application/pdf,image/*,video/*,audio/*|max:5120',
        ]);

        $path = $request->file('file')->store('answer_files', 'public');

        AnswerFile::create([
            'user_id'=> $id,
            'name'   => $request->name,
            'link'   => $path,
            'type'   => $request->file('file')->getClientMimeType(),
        ]);

        return success_response(null, 201, 'ফাইল সেভ হয়েছে।');
    }

    public function destroyAnswerFile($id, $fileId)
    {
        $file = AnswerFile::where('user_id',$id)->find($fileId);
        if (!$file) return error_response(null,404,'ফাইল পাওয়া যায়নি।');

        Storage::disk('public')->delete($file->link);
        $file->delete();

        return success_response(null,200,'ফাইল ডিলিট হয়েছে।');
    }
}
