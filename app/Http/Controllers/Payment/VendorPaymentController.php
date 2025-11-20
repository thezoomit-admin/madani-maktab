<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Traits\HandlesImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorPaymentController extends Controller
{
    use HandlesImageUpload;  
    public function payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:vendors,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        $amount = $request->input('amount');
        $payment_method = PaymentMethod::find($request->input('payment_method_id'));

        if ($amount > $payment_method->expense_in_hand) {
            return error_response(null, 404, $payment_method->name . ' অ্যাকাউন্টে ' . $amount . ' টাকা নেই।');
        }

         $imagePath = $this->uploadImage($request, 'image', 'uploads/due_pay');


        DB::beginTransaction();

        try {
            VendorPayment::create([
                'vendor_id' => $request->input('vendor_id'),
                'payment_method_id' => $request->input('payment_method_id'),
                'amount' => $amount,
                'image' => $imagePath,
                'created_by' => Auth::id(),
            ]);
            

            $payment_method->expense_in_hand -= $amount;
            $payment_method->balance -= $amount;
            $payment_method->save();

            $vendor = Vendor::find($request->input('vendor_id'));
            $vendor->due = $vendor->$vendor - $amount;
            $vendor->save();

            DB::commit(); 
            return success_response(null,'Payment recorded successfully.'); 
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, 'Something went wrong. Please try again.');
        }
    }
}
