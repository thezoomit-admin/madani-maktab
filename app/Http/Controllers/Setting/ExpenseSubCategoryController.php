<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\ExpenseSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseSubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $expense_category_id = $request->expense_category_id;  
        $subCategories = ExpenseSubCategory::query();  
        if ($expense_category_id) { 
            $subCategories->where('category_id', $expense_category_id);
        }  
        $subCategories = $subCategories->get(); 
        return success_response($subCategories);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categoriy_id' => 'required|exists:expense_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        } 
        $subCategory = ExpenseSubCategory::create($request->all());
        return success_response(null, 'Expense subcategory created successfully.');
    }

    public function show($id)
    {
        $subCategory = ExpenseSubCategory::with('category')->find($id);

        if (!$subCategory) {
            return error_response(null, 404, 'Expense subcategory not found.');
        }

        return success_response($subCategory, 'Expense subcategory fetched successfully.');
    }

    public function update(Request $request, $id)
    {
        $subCategory = ExpenseSubCategory::find($id);

        if (!$subCategory) {
            return error_response(null, 404, 'Expense subcategory not found.');
        }

        $validator = Validator::make($request->all(), [
            'categoriy_id' => 'sometimes|required|exists:expense_categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        $subCategory->update($request->all());
        return success_response(null, 'Expense subcategory updated successfully.');
    }

    public function destroy($id)
    {
        $subCategory = ExpenseSubCategory::find($id);

        if (!$subCategory) {
            return error_response(null, 404, 'Expense subcategory not found.');
        }

        $subCategory->delete();
        return success_response(null, 'Expense subcategory deleted successfully.');
    }
}
