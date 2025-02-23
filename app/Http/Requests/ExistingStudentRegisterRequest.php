<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExistingStudentRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
     
            'guardian_name' => 'required|string|max:255',
            'guardian_relation' => 'required|string|max:255',
            'guardian_occupation_details' => 'required|string',
            'guardian_education' => 'required|string', 
            'children_count' => 'required|integer|min:0',
            'child_education' => 'required|json',
            'contact_number_1' => 'required|string|max:15', 
            'contact_number_2' => 'nullable|string|max:15',
            'whatsapp_number' => 'required|string|max:15',
            'house_or_state' => 'required|string|max:255',
            'post_office' => 'required|string|max:255', 
            'upazila_thana' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'same_address' => 'nullable|boolean', 
        ];
    }
}
