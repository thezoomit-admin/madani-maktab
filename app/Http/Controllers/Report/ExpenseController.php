<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        try { 
            $query = Expense::with(['user', 'approvedBy', 'category', 'paymentMethod'])  
                ->select('id', 'user_id', 'expense_category_id', 'payment_method_id', 'amount', 'note', 'image')
                ->latest(); 
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $total = $query->count();  

            $results = $query->skip(($page - 1) * $perPage)   
                            ->take($perPage)  
                            ->get()
                            ->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'user_id' => $item->user_id,
                                    'user_name' => $item->user->name ?? null, 
                                    'expense_category_id' => $item->expense_category_id,
                                    'expense_category_name' => $item->category->name ?? null, 
                                    'payment_method_id' => $item->paymentMethod->id ?? null,  
                                    'payment_method_name' => $item->paymentMethod->name ?? null,  
                                    'amount' => $item->amount,
                                    'note' => $item->note,
                                    'image' => $item->image,
                                ];
                            }); 
            return success_response([
                'data' => $results,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int)$perPage,
                    'current_page' => (int)$page,
                    'last_page' => ceil($total / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to retrieve expenses.');
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        DB::beginTransaction();
        try { 
            $amount = $request->amount;
            $payment_method = PaymentMethod::find($request->payment_method_id);
            if($amount > $payment_method->expense_in_hand){
                return error_response(null,404, $payment_method->name . ' Account এ ' . $amount . ' টাকা নেই।'); 
            }
             
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/expenses'), $imageName);
                $imagePath = asset('uploads/expenses/' . $imageName);
            }

            Expense::create([
                'user_id' => Auth::user()->id,
                'expense_category_id' => $request->expense_category_id,
                'payment_method_id' => $request->payment_method_id,
                'amount' => $request->amount,
                'note' => $request->note,
                'image' => $imagePath,
                'is_approved' => false,
            ]); 
            $payment_method->expense_in_hand -=$amount;
            $payment_method->balance -=$amount;
            $payment_method->save(); 

            DB::commit();
            return success_response(null, 'Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500, 'Failed to create expense.');
        }
    }

    public function show(Expense $expense)
    {
        try {
            $expense->load(['user', 'approvedBy', 'category']);
            return success_response($expense, 'Expense retrieved successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to retrieve expense.');
        }
    }

    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/expenses'), $imageName);
                $expense->image = asset('uploads/expenses/' . $imageName);
            }

            $expense->update([
                'expense_category_id' => $request->expense_category_id,
                'amount' => $request->amount,
                'note' => $request->note,
            ]);

            return success_response($expense, 'Expense updated successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to update expense.');
        }
    }

    public function destroy(Expense $expense)
    {
        try {
            $expense->delete();
            return success_response(null, 'Expense deleted successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to delete expense.');
        }
    }

    public function approve($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $expense->is_approved = true;
            $expense->approved_by = Auth::id();
            $expense->save();

            return success_response($expense, 'Expense approved successfully.');
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500, 'Failed to approve expense.');
        }
    }
}
