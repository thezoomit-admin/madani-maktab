<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator; 
use Illuminate\Http\Exceptions\HttpResponseException;

class ExistingStudentApproveRequest extends FormRequest
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
            'fee_type' => 'required|in:1,2,3',
            'reg_id' => ['required', 'regex:/^[0-9]+$/', 'unique:students,reg_id'],
            'jamaat' => ['nullable', 'regex:/^[0-9]+$/'],
            'department_id' => ['required'],
            'session' => ['required'],
            'fee' => ['nullable', 'numeric', 'min:0'],

            'last_year_department_id' => ['required'],
            'last_year_session' => ['required'],  
        ];
    }

    public function messages(): array
    {
        return [
            'fee_type.required' => 'ফি টাইপ দেওয়া আবশ্যক।',
            'fee_type.in' => 'ফি টাইপ অবশ্যই সাধারণ, আংশিক বা মেহমান হতে হবে।',

            'reg_id.required' => 'রেজিস্ট্রেশন আইডি দেওয়া আবশ্যক।',
            'reg_id.regex' => 'রেজিস্ট্রেশন আইডি অবশ্যই শুধুমাত্র ইংরেজি সংখ্যা (0-9) হতে হবে।',
            'reg_id.unique' => 'এই রেজিস্ট্রেশন আইডি ইতিমধ্যে ব্যবহার করা হয়েছে।',

            'jamaat.regex' => 'জামাত অবশ্যই শুধুমাত্র ইংরেজি সংখ্যা (0-9) হতে হবে।',

            'department_id.required' => 'বর্তমান বিভাগ নির্বাচন করা আবশ্যক।',

            'session.required' => 'বর্তমান সেশন আবশ্যক।',

            'last_year_department_id.required' => 'গত বছরের বিভাগ নির্বাচন করা আবশ্যক।', 

            'last_year_session.required' => 'গত বছরের সেশন দেওয়া আবশ্যক।', 

            'fee.numeric' => 'ফি অবশ্যই সংখ্যা হতে হবে।',
            'fee.min' => 'ফি অবশ্যই ০ বা তার বেশি হতে হবে।',
        ];
    }
}
