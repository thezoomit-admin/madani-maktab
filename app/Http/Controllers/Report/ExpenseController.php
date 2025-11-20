<?php

namespace App\Http\Controllers\Report;

use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use App\Traits\HandlesImageUpload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    use HandlesImageUpload; 
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
                        'expense_category_id' => $item->expense_category_id,
                        'expense_category_name' => optional($item->category)->name,
                        'expense_sub_category_id' => $item->expense_sub_category_id,
                        'expense_sub_category_name' => optional($item->subCategory)->name,
                        'vendor_id' => $item->vendor_id,
                        'payment_method_id' => $item->payment_method_id,
                        'amount' => $item->amount,
                        'total_amount' => $item->total_amount,
                        'description' => $item->description,
                        'measurement' => $item->measurement,
                        'measurment_unit_id' => $item->measurment_unit_id,
                        'measurment_unit' => optional($item->measurmentUnit)->short_name,
                        'voucher_no' => $item->voucher_no,
                        'image' => image_url($item->image),
                        'vendor' => optional($item->vendor)->name,
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

            $imagePath = $this->uploadImage($request, 'image', 'uploads/expenses');
 
            $lastNumber = Expense::whereNotNull('expenses_no')->max('expenses_no');
            $expenses_no = $lastNumber ? $lastNumber + 1 : 1;

            foreach ($request->expenses as $expense) {
                Expense::create([
                    'expenses_no' => $expenses_no,
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
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'expense_sub_category_id' => 'nullable|exists:expense_sub_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'measurement' => 'nullable|string|max:255',
            'measurment_unit_id' => 'nullable|integer|exists:measurment_units,id',
            'image' => 'nullable|image|max:2048',
            'voucher_no' => 'nullable|string|max:255',
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

            // Store old values for balance calculations
            $oldPaymentMethodId = $expense->payment_method_id;
            $oldVendorId = $expense->vendor_id;
            $oldTotalAmount = $expense->total_amount;

            // Get new values from request (use old values if not provided)
            $newPaymentMethodId = $request->has('payment_method_id') 
                ? $request->payment_method_id 
                : $oldPaymentMethodId;
            $newVendorId = $request->has('vendor_id') 
                ? $request->vendor_id 
                : $oldVendorId;
            $newTotalAmount = $request->filled('total_amount') 
                ? $request->total_amount 
                : $oldTotalAmount;

            // Calculate difference in total amount
            $amountDifference = $newTotalAmount - $oldTotalAmount;

            // Handle balance adjustments based on changes
            $paymentMethodChanged = $oldPaymentMethodId != $newPaymentMethodId;
            $vendorChanged = $oldVendorId != $newVendorId;
            $amountChanged = $oldTotalAmount != $newTotalAmount;

            // If payment method or vendor changed, or amount changed, adjust balances
            if ($paymentMethodChanged || $vendorChanged || $amountChanged) {
                // Revert old balances first
                if ($oldVendorId) {
                    // Revert vendor due (subtract old amount)
                    $oldVendor = Vendor::find($oldVendorId);
                    if ($oldVendor) {
                        $oldVendor->due -= $oldTotalAmount;
                        $oldVendor->save();
                    }
                } else if ($oldPaymentMethodId) {
                    // Revert payment method balance (add back old amount)
                    $oldPaymentMethod = PaymentMethod::find($oldPaymentMethodId);
                    if ($oldPaymentMethod) {
                        $oldPaymentMethod->expense_in_hand += $oldTotalAmount;
                        $oldPaymentMethod->balance += $oldTotalAmount;
                        $oldPaymentMethod->save();
                    }
                }

                // Apply new balances
                if ($newVendorId) {
                    // Add to new vendor due
                    $newVendor = Vendor::find($newVendorId);
                    if (!$newVendor) {
                        DB::rollBack();
                        return error_response(null, 404, 'Vendor not found.');
                    }
                    $newVendor->due += $newTotalAmount;
                    $newVendor->save();
                } else if ($newPaymentMethodId) {
                    // Get payment method
                    // If same as old, reuse the instance we already updated
                    if ($oldPaymentMethodId == $newPaymentMethodId && isset($oldPaymentMethod)) {
                        $newPaymentMethod = $oldPaymentMethod;
                    } else {
                        $newPaymentMethod = PaymentMethod::find($newPaymentMethodId);
                        if (!$newPaymentMethod) {
                            DB::rollBack();
                            return error_response(null, 404, 'Payment method not found.');
                        }
                    }

                    // Check if sufficient balance available for the new amount
                    if ($newTotalAmount > 0 && $newTotalAmount > $newPaymentMethod->expense_in_hand) {
                        DB::rollBack();
                        return error_response(null, 400, $newPaymentMethod->name . ' অ্যাকাউন্টে পর্যাপ্ত টাকা নেই।');
                    }

                    // Deduct from payment method balance
                    $newPaymentMethod->expense_in_hand -= $newTotalAmount;
                    $newPaymentMethod->balance -= $newTotalAmount;
                    $newPaymentMethod->save();
                }
            }

            // Handle image upload
            $imagePath = $expense->image; // Keep existing image by default
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($expense->image) {
                    $oldImagePath = public_path('uploads/expenses/' . basename($expense->image));
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }

                // Upload new image
                $imagePath = $this->uploadImage($request, 'image', 'uploads/expenses');
            }

            // Prepare update data
            $updateData = [];
            
            if ($request->filled('expense_category_id')) {
                $updateData['expense_category_id'] = $request->expense_category_id;
            }
            
            if ($request->filled('expense_sub_category_id')) {
                $updateData['expense_sub_category_id'] = $request->expense_sub_category_id;
            }
            
            if ($request->has('vendor_id')) {
                $updateData['vendor_id'] = $request->vendor_id;
            }
            
            if ($request->has('payment_method_id')) {
                $updateData['payment_method_id'] = $request->payment_method_id;
            }
            
            if ($request->has('amount')) {
                $updateData['amount'] = $request->amount;
            }
            
            if ($request->filled('total_amount')) {
                $updateData['total_amount'] = $request->total_amount;
            }
            
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }
            
            if ($request->has('measurement')) {
                $updateData['measurement'] = $request->measurement;
            }
            
            if ($request->has('measurment_unit_id')) {
                $updateData['measurment_unit_id'] = $request->measurment_unit_id;
            }
            
            if ($request->has('voucher_no')) {
                $updateData['voucher_no'] = $request->voucher_no;
            }
            
            if ($request->hasFile('image')) {
                $updateData['image'] = $imagePath;
            }

            // Update expense
            $expense->update($updateData);

            // Reload relationships
            $expense->load(['user', 'approvedBy', 'category', 'subCategory', 'paymentMethod', 'measurmentUnit', 'vendor']);

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
