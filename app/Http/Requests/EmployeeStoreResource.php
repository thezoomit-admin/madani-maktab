<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmployeeStoreResource extends FormRequest
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
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email',
            'user_phone' => 'required|string|max:15|unique:users,phone', 
            'role_id' => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', 
            
            'name' => 'nullable|string|max:255', 
            'office_phone' => 'nullable|string|max:15',
            'personal_phone' => 'nullable|string|max:15',
            'office_email' => 'nullable|email|max:45',
            'personal_email' => 'nullable|email|max:45',
            'emergency_contact_number' => 'nullable|string|max:15',
            'emergency_contact_person' => 'nullable|string|max:255',
             
            'country_id' => 'nullable|exists:countries,id',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'upazila_id' => 'nullable|exists:upazilas,id',
            'address' => 'nullable|string|max:250',
             
            'designation_id' => 'required|exists:designations,id',
            'reporting_user_id' => 'nullable|exists:users,id',
        ];
    }
}
