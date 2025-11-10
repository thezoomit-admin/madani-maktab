<?php

namespace App\Http\Requests\Admin\Admission;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DirectAdmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic Information
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'contact_number_1' => 'required|string|max:15',
            'contact_number_2' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'dob' => 'required|date',
            'dob_hijri' => 'required|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'reg_id' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // Student Information
            'department_id' => 'required|integer|in:1,2', 
            'jamaat' => 'nullable|string|max:255',
            'average_marks' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|integer|in:0,1,2',
            'previous_education_details' => 'nullable|string',
            'bangla_study_status' => 'nullable|string|max:255',
            'bangla_others_study' => 'nullable|string|max:255',
            'arabi_study_status' => 'nullable|string|max:255',
            'arabi_others_study' => 'nullable|string|max:255',
            'hifz_para' => 'nullable|string|max:255',
            'is_other_kitab_study' => 'nullable|integer|in:0,1',
            'kitab_jamat' => 'nullable|string|max:255',
            'is_bangla_handwriting_clear' => 'nullable|integer|in:0,1',
            'major_illness_history' => 'nullable|string',
            'current_medication_details' => 'nullable|string',
            'handwriting_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            
            // Guardian Information
            'guardian_name' => 'required|string|max:255',
            'guardian_relation' => 'required|string|max:255',
            'guardian_occupation_details' => 'required|string',
            'guardian_education' => 'required|string|max:255',
            'children_count' => 'nullable|integer|min:0',
            'child_education' => 'nullable|array',
            'child_education.*' => 'nullable|string|max:255',
            'whatsapp_number' => 'required|string|max:15',
            'same_address' => 'nullable|boolean',
            
            // Permanent Address
            'house_or_state' => 'required|string|max:255',
            'village_or_area' => 'nullable|string|max:255',
            'post_office' => 'required|string|max:255',
            'upazila_thana' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            
            // Temporary Address (required if same_address is false)
            'temporary_house_or_state' => 'required_if:same_address,false|nullable|string|max:255',
            'temporary_village_or_area' => 'nullable|string|max:255',
            'temporary_post_office' => 'required_if:same_address,false|nullable|string|max:255',
            'temporary_upazila_thana' => 'nullable|string|max:255',
            'temporary_district' => 'required_if:same_address,false|nullable|string|max:255',
            'temporary_division' => 'required_if:same_address,false|nullable|string|max:255',
            
            // Enrollment Information
            'roll_number' => 'nullable|integer',
            'session' => 'required|string|max:255',
            'fee_type' => 'required|integer|in:1,2,3,4,5',
            'fee' => 'required_if:fee_type,2|required_if:fee_type,4|nullable|numeric|min:0',
            'admission_fee' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Basic Information
            'name.required' => 'নাম আবশ্যক',
            'contact_number_1.required' => 'যোগাযোগ নম্বর ১ আবশ্যক',
            'dob.required' => 'জন্ম তারিখ আবশ্যক',
            'dob.date' => 'জন্ম তারিখ সঠিক ফরম্যাটে দিন',
            'dob_hijri.required' => 'হিজরি জন্ম তারিখ আবশ্যক',
            'email.email' => 'ইমেইল সঠিক ফরম্যাটে দিন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            
            // Student Information
            'department_id.required' => 'বিভাগ নির্বাচন করুন',
            'department_id.in' => 'বিভাগ সঠিক নয়',
            'father_name.required' => 'পিতার নাম আবশ্যক',
            'average_marks.numeric' => 'গড় নম্বর সংখ্যা হতে হবে',
            'average_marks.min' => 'গড় নম্বর ০ এর কম হতে পারবে না',
            'average_marks.max' => 'গড় নম্বর ১০০ এর বেশি হতে পারবে না',
            'status.in' => 'স্ট্যাটাস সঠিক নয়',
            
            // Guardian Information
            'guardian_name.required' => 'অভিভাবকের নাম আবশ্যক',
            'guardian_relation.required' => 'সম্পর্ক আবশ্যক',
            'guardian_occupation_details.required' => 'পেশার বিবরণ আবশ্যক',
            'guardian_education.required' => 'শিক্ষাগত যোগ্যতা আবশ্যক',
            'children_count.integer' => 'সন্তান সংখ্যা সংখ্যা হতে হবে',
            'children_count.min' => 'সন্তান সংখ্যা ০ এর কম হতে পারবে না',
            'whatsapp_number.required' => 'WhatsApp নম্বর আবশ্যক',
            'child_education.array' => 'সন্তানের শিক্ষাগত অবস্থা একটি অ্যারে হতে হবে',
            
            // Permanent Address
            'house_or_state.required' => 'বাসা/সড়ক আবশ্যক',
            'post_office.required' => 'ডাকঘর আবশ্যক',
            'district.required' => 'জেলা আবশ্যক',
            'division.required' => 'বিভাগ আবশ্যক',
            
            // Temporary Address
            'temporary_house_or_state.required_if' => 'অস্থায়ী ঠিকানার বাসা/সড়ক আবশ্যক',
            'temporary_post_office.required_if' => 'অস্থায়ী ঠিকানার ডাকঘর আবশ্যক',
            'temporary_district.required_if' => 'অস্থায়ী ঠিকানার জেলা আবশ্যক',
            'temporary_division.required_if' => 'অস্থায়ী ঠিকানার বিভাগ আবশ্যক',
            
            // Enrollment Information
            'session.required' => 'সেশন আবশ্যক',
            'fee_type.required' => 'ফি টাইপ আবশ্যক',
            'fee_type.in' => 'ফি টাইপ সঠিক নয়',
            'fee.required_if' => 'আংশিক ফি টাইপের জন্য ফি পরিমাণ আবশ্যক',
            'fee.numeric' => 'ফি পরিমাণ সংখ্যা হতে হবে',
            'fee.min' => 'ফি পরিমাণ ০ এর কম হতে পারবে না',
            'admission_fee.numeric' => 'ভর্তি ফি সংখ্যা হতে হবে',
            'admission_fee.min' => 'ভর্তি ফি ০ এর কম হতে পারবে না',
            'roll_number.integer' => 'রোল নম্বর সংখ্যা হতে হবে',
            
            // File Validation
            'profile_image.image' => 'প্রোফাইল ছবি একটি ইমেজ ফাইল হতে হবে',
            'profile_image.mimes' => 'প্রোফাইল ছবি jpg, jpeg, বা png ফরম্যাটে হতে হবে',
            'profile_image.max' => 'প্রোফাইল ছবি ২MB এর বেশি হতে পারবে না',
            'handwriting_image.image' => 'হাতের লেখার ছবি একটি ইমেজ ফাইল হতে হবে',
            'handwriting_image.mimes' => 'হাতের লেখার ছবি jpg, jpeg, বা png ফরম্যাটে হতে হবে',
            'handwriting_image.max' => 'হাতের লেখার ছবি ২MB এর বেশি হতে পারবে না',
        ];
    }
}

