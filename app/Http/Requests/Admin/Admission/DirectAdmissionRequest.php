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
            'name' => 'required|string|max:255',
            'reg_id' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'dob_hijri' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'email' => 'nullable|email|max:255',
     
            // STUDENT INFORMATION 
            'department_id' => 'required|integer|in:1,2',
            'jamaat' => 'nullable|string|max:255',
            'average_marks' => 'nullable|numeric|min:0|max:100',
     
            // ENROLLMENT INFORMATION 
            'roll_number' => 'required|string|max:255',
            'session' => 'required|string|max:255',
            'fee_type' => 'required|integer|in:1,2,3,4,5',
            'fee' => 'nullable|numeric|min:0',
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
            'dob.required' => 'জন্ম তারিখ আবশ্যক',
            'dob.date' => 'জন্ম তারিখ সঠিক ফরম্যাটে দিন',
            'dob_hijri.required' => 'হিজরি জন্ম তারিখ আবশ্যক',
            'email.email' => 'ইমেইল সঠিক ফরম্যাটে দিন',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে',
            'profile_image.image' => 'প্রোফাইল ছবি একটি ইমেজ ফাইল হতে হবে',
            'profile_image.mimes' => 'প্রোফাইল ছবি jpg, jpeg, বা png ফরম্যাটে হতে হবে',
            'profile_image.max' => 'প্রোফাইল ছবি ২MB এর বেশি হতে পারবে না',

            // ============================================
            // STUDENT INFORMATION
            // ============================================
            'department_id.required' => 'বিভাগ নির্বাচন করুন',
            'department_id.in' => 'বিভাগ সঠিক নয়',
            'average_marks.numeric' => 'গড় নম্বর সংখ্যা হতে হবে',
            'average_marks.min' => 'গড় নম্বর ০ এর কম হতে পারবে না',
            'average_marks.max' => 'গড় নম্বর ১০০ এর বেশি হতে পারবে না',

            // ============================================
            // ENROLLMENT INFORMATION
            // ============================================
            'roll_number.required' => 'রোল নম্বর আবশ্যক',
            'roll_number.integer' => 'রোল নম্বর সংখ্যা হতে হবে',
            'session.required' => 'সেশন আবশ্যক',
            'fee_type.required' => 'ফি টাইপ আবশ্যক',
            'fee_type.in' => 'ফি টাইপ সঠিক নয়',
            'fee.required_if' => 'আংশিক ফি টাইপের জন্য ফি পরিমাণ আবশ্যক',
            'fee.numeric' => 'ফি পরিমাণ সংখ্যা হতে হবে',
            'fee.min' => 'ফি পরিমাণ ০ এর কম হতে পারবে না',
            'admission_fee.numeric' => 'ভর্তি ফি সংখ্যা হতে হবে',
            'admission_fee.min' => 'ভর্তি ফি ০ এর কম হতে পারবে না',
        ];
    }
}

