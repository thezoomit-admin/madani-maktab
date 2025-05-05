<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = ExpenseCategory::select('id','name','description')->get();
            return success_response($categories);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to retrieve expense categories.');
        }
    }

    public function store(Request $request)
    { 
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $category = ExpenseCategory::create([
                'name' => $request->name,
                'description' => $request->description,
            ]); 
            return success_response(null, 'Expense category created successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to create expense category.');
        }
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        try {
            return success_response($expenseCategory);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to retrieve expense category.');
        }
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $expenseCategory->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return success_response(null, 'Expense category updated successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to update expense category.');
        }
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        try {
            $expenseCategory->delete();
            return success_response(null, 'Expense category deleted successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to delete expense category.');
        }
    }
}
