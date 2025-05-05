<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\OfficeTransaction;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function incomeBalance()
    {
        $payment_methods = PaymentMethod::select('id','icon','name','income_in_hand as balance')->get();
        $total = $payment_methods->sum('income_in_hand'); 
        return success_response([
            'payment_methods' => $payment_methods,
            'total_income_in_hand' => $total,
        ]);
    } 

    

    /**
     * Store a withdraw transaction (Withdraw for office expense).
     */
    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payer_account' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $imagePath = $this->uploadImage($request, 'image', 'uploads/transactions');

            $paymentMethod = PaymentMethod::findOrFail($validated['payment_method_id']);

            if ($validated['amount'] > $paymentMethod->balance) {
                return response()->json([
                    'status' => false,
                    'message' => $paymentMethod->name . ' bank এ ' . $validated['amount'] . ' টাকা নেই।'
                ], 404);
            }

            $transaction = OfficeTransaction::create([
                'type' => 2,
                'payment_method_id' => $validated['payment_method_id'],
                'payer_account' => $validated['payer_account'] ?? null,
                'amount' => $validated['amount'],
                'image' => $imagePath,
                'is_approved' => true,
                'approved_by' => Auth::id(),
            ]);

            $paymentMethod->expense_in_hand += $validated['amount'];
            $paymentMethod->balance -= $validated['amount'];
            $paymentMethod->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Withdraw recorded successfully.',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Withdraw failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Common method for uploading images.
     */
    private function uploadImage(Request $request, string $inputName, string $folder)
    {
        if ($request->hasFile($inputName)) {
            $image = $request->file($inputName);
            $imageName = time() . '_' . $image->getClientOriginalName();
            $uploadPath = public_path($folder);

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            $image->move($uploadPath, $imageName);
            return asset($folder . '/' . $imageName);
        }

        return null;
    }

}
