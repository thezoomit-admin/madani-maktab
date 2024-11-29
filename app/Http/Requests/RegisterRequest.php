<?php
 namespace App\Http\Requests;

 use Illuminate\Contracts\Validation\Validator;
 use Illuminate\Foundation\Http\FormRequest;
 use Illuminate\Http\Exceptions\HttpResponseException;
 
 class RegisterRequest extends FormRequest
 {
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
 
     public function authorize(): bool
     {
         return true;
     }
 
     public function rules(): array
     {
         return [
             'company_name' => 'required|string|max:255',
             'website' => 'nullable|url|max:255',
             'address' => 'nullable|string|max:500',
             'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
             'primary_color' => 'nullable|string|max:7',
             'secondary_color' => 'nullable|string|max:7',
             'founded_date' => 'nullable|date',
             'category_id' => 'required|exists:company_categories,id',
             
             // User Validation
             'user_name' => 'required|string|max:255',
             'user_email' => 'required|email|unique:users,email',
             'user_phone' => 'required|string|max:15|unique:users,phone',
             'password' => 'required|string|min:8',
             'role_id' => 'required|exists:roles,id',
             
             // User Address Validation
             'country_id' => 'nullable|exists:countries,id',
             'division_id' => 'nullable|exists:divisions,id',
             'district_id' => 'nullable|exists:districts,id',
             'upazila_id' => 'nullable|exists:upazilas,id',
             'address' => 'nullable|string|max:250',
             
             // User Contact Validation
             'office_phone' => 'nullable|string|max:15',
             'personal_phone' => 'nullable|string|max:15',
             'office_email' => 'nullable|string|max:45',
             'personal_email' => 'nullable|string|max:45',
             'emergency_contact_number' => 'nullable|string|max:15',
             'emergency_contact_person' => 'nullable|string|max:45',
         ];
     }
 }
 