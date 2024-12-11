<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'description' => 'nullable|string',
            'regular_price' => 'required|numeric|min:0', 
            'sell_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
        ];
    }
}
