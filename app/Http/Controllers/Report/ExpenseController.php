<?php

namespace App\Http\Controllers\Report;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{ 
    public function index(Request $request)
    {
        $month = $request->filled('month_id')
            ? \App\Models\HijriMonth::find($request->month_id)
            : null;
        if ($month) {
            $startDate = Carbon::parse($month->start_date)->startOfDay();
            $endDate = Carbon::parse($month->end_date)->endOfDay();
        } else {
            $endDate = Carbon::now()->endOfDay();
            $startDate = $endDate->copy()->subDays(30)->startOfDay();
        }

        try {
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            // Main query with filters
            $query = Expense::with(['user', 'approvedBy', 'category', 'subCategory', 'paymentMethod', 'measurmentUnit', 'vendor'])
                ->select('id', 'user_id', 'expense_category_id', 'expense_sub_category_id', 'vendor_id', 'payment_method_id',
                        'amount', 'total_amount', 'description', 'measurement', 'measurment_unit_id', 'image', 'created_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($request->filled('expense_category_id'), function ($q) use ($request) {
                    $q->where('expense_category_id', $request->expense_category_id);
                })
                ->when($request->filled('expense_sub_category_id'), function ($q) use ($request) {
                    $q->where('expense_sub_category_id', $request->expense_sub_category_id);
                })
                ->when($request->filled('vendor_id'), function ($q) use ($request) {
                    $q->where('vendor_id', $request->vendor_id);
                })
                ->when($request->filled('keyword'), function ($q) use ($request) {
                    $q->where('description', $request->keyword);
                })
                ->latest();

            // Count total before pagination
            $total = $query->count();  

            // Apply pagination
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
                        'voucher_no' => $item->voucher_no,
                    ];
                });

            // Success response
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
                    'total_amount' => $expense['total'] ?? 0,
                    'description' => $expense['description'] ?? null,
                    'measurement' => $expense['measurement'] ?? null,
                    'measurment_unit_id' => $expense['measurement_unit_id'] ?? null,
                    'image' => $imagePath,
                    'voucher_no' => $request->voucher_no,
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'measurement'   => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'total_amount'  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        DB::beginTransaction();
        try {
            $expense = Expense::find($id);
            if (!$expense) {
                return error_response(null, 404, 'Expense not found.');
            }

            $payment_method = PaymentMethod::find($expense->payment_method_id);
            $vendor = $expense->vendor_id ? Vendor::find($expense->vendor_id) : null;

            // Old and new values
            $oldTotal = $expense->total_amount;
            $newAmount = $request->amount;
            $newTotal = $request->total_amount;

            // Calculate difference
            $difference = $newTotal - $oldTotal;

            if (!$vendor) {
                // Payment method update
                if ($difference > 0 && $difference > $payment_method->expense_in_hand) {
                    return error_response(null, 400, $payment_method->name . ' অ্যাকাউন্টে পর্যাপ্ত টাকা নেই।');
                }

                // Update balances (difference can be + or -)
                $payment_method->expense_in_hand -= $difference;
                $payment_method->balance -= $difference;
                $payment_method->save();
            } else {
                // Vendor due adjustment (difference can be + or -)
                $vendor->due += $difference;
                if ($vendor->due < 0) {
                    $vendor->due = 0; // Prevent negative due
                }
                $vendor->save();
            }

            // Update expense fields
            $expense->update([
                'measurement'  => $request->measurement,
                'amount'       => $newAmount,
                'total_amount' => $newTotal,
            ]);

            DB::commit();
            return success_response($expense, 'Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500, 'Failed to update expense.');
        }
    }

    public function destroy(Expense $expense)
    {
        DB::beginTransaction();
        try {
            if (!$expense) {
                return error_response(null, 404, 'Expense not found.');
            }

            $payment_method = PaymentMethod::find($expense->payment_method_id);
            $vendor = $expense->vendor_id ? Vendor::find($expense->vendor_id) : null;

            $totalAmount = $expense->total_amount;

            if (!$vendor) { 
                $payment_method->expense_in_hand += $totalAmount;
                $payment_method->balance += $totalAmount;
                $payment_method->save();
            } else { 
                $vendor->due -= $totalAmount;
                if ($vendor->due < 0) {
                    $vendor->due = 0; 
                }
                $vendor->save();
            }  

            if ($expense->image) {
                $imagePath = public_path('uploads/expenses/' . basename($expense->image));
                if (file_exists($imagePath)) {
                    @unlink($imagePath);
                }
            }
 
            $expense->delete();

            DB::commit();
            return success_response(null, 'Expense deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
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
