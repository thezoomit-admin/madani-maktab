<?php

namespace App\Http\Controllers\Report;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    
    
     public function index(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            // Build base query with relationships and selected fields
            $query = Expense::with(['user', 'approvedBy', 'category', 'subCategory', 'paymentMethod', 'measurmentUnit', 'vendor'])
                ->select('id', 'user_id', 'expense_category_id', 'expense_sub_category_id', 'vendor_id', 'payment_method_id',
                        'amount', 'total_amount', 'description', 'measurement', 'measurment_unit_id', 'image', 'created_at')
                ->latest();

            $total = $query->count();

            // Get paginated results and transform
            $results = $query->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'month' => app(HijriDateService::class)->getHijri($item->created_at),  
                        'expense_category_name' => optional($item->category)->name,
                        'expense_sub_category_name' => optional($item->subCategory)->name,
                        'description' => $item->description,
                        'measurement' => $item->measurement,
                        'measurment_unit' => optional($item->measurmentUnit)->short_name,
                        'amount' => $item->amount,
                        'total_amount' => $item->total_amount,
                        'vendor' => optional($item->vendor)->name,
                        'image' => $item->image,
                    ];
                });

            // Return formatted response
            return success_response([
                'data' => $results,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
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
            'expense_sub_category_id' => 'required|exists:expense_sub_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'payment_method_id' => 'required|exists:payment_methods,id',

            'expenses' => 'required|array|min:1',
            'expenses.*.description' => 'nullable|string|max:1000',
            'expenses.*.measurement' => 'nullable|string|max:255',
            'expenses.*.measurement_unit_id' => 'nullable|integer|exists:measurment_units,id',
            'expenses.*.amount' => 'nullable|numeric|min:0',

            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        DB::beginTransaction();
        try {
            $payment_method = PaymentMethod::find($request->payment_method_id);
            $totalExpenses = array_sum(array_column($request->expenses, 'amount'));

            $vendor_id = $request->vendor_id;
            if (!$vendor_id) {
                if ($totalExpenses > $payment_method->expense_in_hand) {
                    return error_response(null, 404, $payment_method->name . ' অ্যাকাউন্টে ' . $totalExpenses . ' টাকা নেই।');
                }
            } else {
                $vendor = Vendor::find($vendor_id);
                if (!$vendor) {
                    return error_response(null, 404, "Vendor not found");
                }
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('uploads/expenses'), $imageName);
                $imagePath = asset('uploads/expenses/' . $imageName);
            }

            foreach ($request->expenses as $expense) {
                Expense::create([
                    'user_id' => Auth::id(),
                    'expense_category_id' => $request->expense_category_id,
                    'expense_sub_category_id' => $request->expense_sub_category_id,
                    'vendor_id' => $vendor_id,
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $expense['amount'] ?? null,
                    'total_amount' => $expense['amount'] ?? 0,
                    'description' => $expense['description'] ?? null,
                    'measurement' => $expense['measurement'] ?? null,
                    'measurment_unit_id' => $expense['measurement_unit_id'] ?? null,
                    'image' => $imagePath,
                    'is_approved' => true,
                ]);
            }

            if (!$vendor_id) {
                $payment_method->expense_in_hand -= $totalExpenses;
                $payment_method->balance -= $totalExpenses;
                $payment_method->save();
            } else {
                $vendor->due += $totalExpenses;
                $vendor->save();
            }

            DB::commit();
            return success_response(null, 'Expenses created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500, 'Failed to create expenses.');
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
            'description' => 'nullable|string',
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
                'description' => $request->description,
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
