<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\OfficeTransaction;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Traits\HandlesImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    use HandlesImageUpload;
    public function index()
    {
        $paymentMethods = PaymentMethod::all();

        $totalIncome = $paymentMethods->sum('income_in_hand');
        $totalExpense = $paymentMethods->sum('expense_in_hand');
        $totalBalance = $paymentMethods->sum('balance');

        return success_response([
            'payment_methods' => $paymentMethods,
            'summary' => [
                'total_income_in_hand' => $totalIncome,
                'total_expense_in_hand' => $totalExpense,
                'total_balance' => $totalBalance,
            ]
        ]);
    } 
    
    public function store(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'info' => 'nullable',
            'income_in_hand' => 'nullable|numeric',
            'expense_in_hand' => 'nullable|numeric',
            // 'balance' => 'nullable|numeric',
        ]);
    
        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'ভ্যালিডেশন ব্যর্থ হয়েছে');
        }
    
        $iconPath = $this->uploadImage($request, 'icon', 'uploads/payment_icons');
    
        PaymentMethod::create([
            'name' => $request->name,
            'icon' => $iconPath,
            'info' => $request->info,
            // 'income_in_hand' => $request->income_in_hand??0,
            // 'expense_in_hand' => $request->expense_in_hand??0,
            // 'balance' => $request->balance??0,
        ]);
    
        return success_response(null, "সফলভাবে তৈরি হয়েছে");
    }
    
    public function update(Request $request, $id)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'info' => 'nullable',
            'income_in_hand' => 'nullable|numeric',
            'expense_in_hand' => 'nullable|numeric',
            // 'balance' => 'nullable|numeric',
        ]);
    
        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'ভ্যালিডেশন ব্যর্থ হয়েছে');
        }
    
        $paymentMethod = PaymentMethod::findOrFail($id);
        if(!$paymentMethod->is_changeable){
            return error_response(null, 403, "এই পেমেন্ট মেথডটি পরিবর্তনযোগ্য নয়");
        }
    
        if ($request->hasFile('icon')) { 
            if ($paymentMethod->icon) {
                $oldIconPath = public_path($paymentMethod->icon);
                if (file_exists($oldIconPath)) {
                    unlink($oldIconPath);
                }
            }
    
            $paymentMethod->icon = $this->uploadImage($request, 'icon', 'uploads/payment_icons');
        }
    
        $paymentMethod->update([
            'name' => $request->name,
            'info' => $request->info,
            // 'income_in_hand' => $request->income_in_hand??0,
            // 'expense_in_hand' => $request->expense_in_hand??0,
            // 'balance' => $request->balance??0,
        ]);
    
        return success_response(null, "সফলভাবে আপডেট হয়েছে");
    }
     
    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        if(!$paymentMethod->is_changeable){
            return error_response(null, 403, "এই পেমেন্ট মেথড ডিলিটযোগ্য নয়");
        }

        $payment_transaction = PaymentTransaction::where('payment_method_id', $id)->count();
        $office_transaction = OfficeTransaction::where('payment_method_id',$id)->count();
        
        if ($payment_transaction > 0 || $office_transaction > 0) {
            return error_response(null, 404, "এই পেমেন্ট মেথডে ট্রান্সঅ্যাকশন আছে, তাই ডিলিট করা যাবে না");
        }
    
        if ($paymentMethod->icon) {
            $oldIconPath = public_path($paymentMethod->icon);
        if (file_exists($oldIconPath)) {
                unlink($oldIconPath);
            }
        } 
    
        $paymentMethod->delete();
    
        return success_response(null, "সফলভাবে মুছে ফেলা হয়েছে");
    }
}
