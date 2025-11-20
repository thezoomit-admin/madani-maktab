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
            'user_email' => 'required|email',
            'user_phone' => 'required|string|max:15', 
            'role_id' => 'required|exists:roles,id',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  
        ];
    }
}
