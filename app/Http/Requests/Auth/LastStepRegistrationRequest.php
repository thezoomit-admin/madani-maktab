<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LastStepRegistrationRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'deeni_steps' => 'required|string|max:255',
            'is_follow_porada' => 'required|boolean',
            'is_shariah_compliant' => 'required|boolean',
            'motivation' => 'required|string|max:255',
            'info_src' => 'required|string|max:255',
            'first_contact' => 'required|date',
            'preparation' => 'nullable|string|max:255',
            'is_clean_lang' => 'required|boolean',
            'future_plan' => 'nullable|string|max:255',
            'years_at_inst' => 'required|integer|min:0',
            'reason_diff_edu' => 'nullable|string|max:2048',
        ];
    }
}
