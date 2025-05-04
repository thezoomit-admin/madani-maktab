<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::all();
        return success_response($paymentMethods);
    }
    
    public function store(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'info' => 'nullable|string',
            'income_in_hand' => 'required|numeric',
            'expense_in_hand' => 'required|numeric',
            'balance' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'ভ্যালিডেশন ব্যর্থ হয়েছে');
        }
    
        $iconPath = null;
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $iconName = time() . '_' . $icon->getClientOriginalName();
            $iconPath = $icon->move(public_path('uploads/payment_icons'), $iconName);
            $iconPath = asset('uploads/payment_icons/' . $iconName); 
        }
    
        PaymentMethod::create([
            'name' => $request->name,
            'icon' => $iconPath,
            'info' => $request->info,
            'income_in_hand' => $request->income_in_hand??0,
            'expense_in_hand' => $request->expense_in_hand??0,
            'balance' => $request->balance??0,
        ]);
    
        return success_response(null, "সফলভাবে তৈরি হয়েছে");
    }
    
    public function update(Request $request, $id)
    { 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  
            'info' => 'nullable|string',
            'income_in_hand' => 'required|numeric',
            'expense_in_hand' => 'required|numeric',
            'balance' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'ভ্যালিডেশন ব্যর্থ হয়েছে');
        }
    
        $paymentMethod = PaymentMethod::findOrFail($id);
    
        if ($request->hasFile('icon')) { 
            if ($paymentMethod->icon) {
                $oldIconPath = public_path(str_replace(asset(''), '', $paymentMethod->icon));
                if (file_exists($oldIconPath)) {
                    unlink($oldIconPath);
                }
            }
    
            $icon = $request->file('icon');
            $iconName = time() . '_' . $icon->getClientOriginalName();
            $icon->move(public_path('uploads/payment_icons'), $iconName);
            $paymentMethod->icon = asset('uploads/payment_icons/' . $iconName);
        }
    
        $paymentMethod->update([
            'name' => $request->name,
            'info' => $request->info,
            'income_in_hand' => $request->income_in_hand??0,
            'expense_in_hand' => $request->expense_in_hand??0,
            'balance' => $request->balance??0,
        ]);
    
        return success_response(null, "সফলভাবে আপডেট হয়েছে");
    }
     
    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $payment_transaction = PaymentTransaction::where('payment_method_id', $id)->count();
        
        if ($payment_transaction > 0) {
            return error_response(null, 404, "এই পেমেন্ট মেথডে ট্রান্সঅ্যাকশন আছে, তাই ডিলিট করা যাবে না");
        }
    
        if ($paymentMethod->icon) {
            $oldIconPath = public_path(str_replace(asset(''), '', $paymentMethod->icon));
            if (file_exists($oldIconPath)) {
                unlink($oldIconPath);
            }
        } 
    
        $paymentMethod->delete();
    
        return success_response(null, "সফলভাবে মুছে ফেলা হয়েছে");
    }
}
