<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FirstStepRegistrationRequst extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     
    public function authorize(): bool
    {
        return true;
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
            'father_name' => 'required|string|max:255',
            'dob' => 'required|string|max:255',
            'dob_hijri' => 'required|string|max:255',
            'department_id' => 'required|integer',
            'bangla_study_status' => 'required|string|max:255',
            'bangla_others_study' => 'nullable|string|max:255',
            'arabi_study_status' => 'required|string|max:255',
            'arabi_others_study' => 'nullable|string|max:255',
            'study_info_after_seven' => 'required|string|max:255', 
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            // 'handwriting_images' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',  
            'previous_institution'  => 'required_if:department_id,1|string|max:255',
            'hifz_para'             => 'nullable|integer',
            'is_other_kitab_study' => 'required_if:department_id,2|integer',
            'kitab_jamat' => 'required_if:is_other_kitab_study,1',
            'is_bangla_handwriting_clear' => 'required_if:department_id,2|integer',
            'kitab_read' => 'required_if:department_id,2|string',
            'guardian_name' => 'required|string|max:255',
            'guardian_relation' => 'required|string|max:255',
            'guardian_occupation' => 'required|string|max:255',
            'guardian_education' => 'required|string|max:255',
            'guardian_workplace' => 'required|string|max:255',
            'children_count' => 'required|integer|min:0',
            'child_1_education' => 'required|string|max:255',
            'contact_number_1' => 'required|string|max:15|unique:users,phone', 
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
        ];
    }
}
