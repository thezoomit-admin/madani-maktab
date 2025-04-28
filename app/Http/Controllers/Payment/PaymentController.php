<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function payNow(Request $request)
    {
        try {
            $payment_id = $request->payment_id;
            $payment = Payment::find($payment_id);

            if (!$payment) {
                return error_response(null, 404, 'পেমেন্ট আইডি সঠিক নয়।');
            }  

            $existingTransaction = PaymentTransaction::where('payment_id', $payment->id)
                ->first();

            if ($existingTransaction) {
                if ($existingTransaction->is_approved) {
                    return error_response(null, 400, 'পেমেন্ট ইতিমধ্যে সম্পন্ন হয়েছে।');
                } else {
                    return error_response(null, 400, 'পেমেন্ট অনুরোধ জমা আছে, অনুমোদনের জন্য অপেক্ষা করুন।');
                }
            }


            $user = User::find(Auth::user()->id);
            $is_approved = false;
            $approved_by = null;
            if($user->user_type=='teacher'){
                $is_approved = true;
                $approved_by = $user->id;
                $payment->paid = $payment->amount;
                $payment->due = 0;
                $payment->save(); 
            }  

             PaymentTransaction::create([
                'user_id' => $payment->user_id,
                'student_id' => $payment->student_id,
                'payment_id' => $payment->id,
                'payer_phone' => $request->payer_phone,
                'transaction_id' => uniqid('TRX'),
                'payment_method' => $request->payment_method ?? 'ম্যানুয়াল',
                'amount' => $payment->amount,
                'is_approved' => $is_approved,
                'approved_by' => $approved_by,
            ]);

            return success_response(null, 'পেমেন্ট অনুরোধ সফলভাবে জমা হয়েছে। অনুমোদনের জন্য অপেক্ষা করুন।');
        } catch (\Exception $e) {
            return error_response(null, 500, $e->getMessage());
        }
    }  

    public function paymentList(Request $request)
    {
        $approve_status = $request->approve_status;  
        $reg_id = $request->reg_id;  
        $perPage = $request->per_page ?? 20;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $perPage; 
        
        $query = PaymentTransaction::with(['user:id,name,reg_id', 'student:id'])->latest();
 
        if ($approve_status !== null) {
            $query->where('is_approved', $approve_status);
        }  

        if ($reg_id) {
            $query->whereHas('user', function ($query) use ($reg_id) {
                $query->where('reg_id', $reg_id);
            });
        }
 
        $total = $query->count();
        $data = $query->skip($offset)
                    ->take($perPage)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id'                => @$item->id,
                            'name'              => @$item->user->name,
                            'reg_id'            => @$item->user->reg_id, 
                            'payment_method'    => @$item->payment_method,
                            'payer_account'     => @$item->payer_account,
                            'amount'            => $item->amount,
                            'is_approved'       => $item->is_approved,
                        ];
                    });

        // Return the response with paginated data
        return success_response([
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => (int) $perPage,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    } 

    public function approvePayment($id)
    {
        try {
            DB::beginTransaction();
    
            $paymentTransaction = PaymentTransaction::find($id);
            if (!$paymentTransaction) {
                return error_response(null, 404, "পেমেন্ট ডাটা খুঁজে পাওয়া যায়নি।");
            }
     
            $paymentTransaction->is_approved = true;
            $paymentTransaction->approved_by = Auth::id();
            $paymentTransaction->save();
     
            $payment = Payment::find($paymentTransaction->payment_id);
            if (!$payment) {
                DB::rollBack();
                return error_response(null, 404, "পেমেন্ট রেকর্ড খুঁজে পাওয়া যায়নি।");
            }
    
            $payment->paid = $payment->amount;
            $payment->due = 0;
            $payment->save();
    
            DB::commit();
    
            return success_response(null, "পেমেন্ট অনুমোদন সম্পূর্ণ হয়েছে।");
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response(null, 500, "কোনো একটি সমস্যা হয়েছে: " . $e->getMessage());
        }
    }

    
}
 