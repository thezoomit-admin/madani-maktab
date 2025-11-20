<?php

namespace App\Http\Controllers\Payment;

use App\Enums\ArabicMonth;
use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\OfficeTransaction;
use App\Models\PaymentMethod;
use App\Traits\HandlesImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfficeTransactionController extends Controller
{
    use HandlesImageUpload;
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string', 
            'image' => 'nullable|image|max:2048',
        ]);  

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        } 
 
        DB::beginTransaction();
        try {
           
            $amount = $request->amount;
            $imagePath= null;
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request, 'image', 'uploads/transactions');
            }  
            
            $paymentMethod = PaymentMethod::find($request->payment_method_id); 
            if ($amount > $paymentMethod->income_in_hand) { 
                return error_response(null,404, $paymentMethod->name . ' Account এ ' . $amount . ' টাকা নেই।'); 
            }  

            OfficeTransaction::create([
                'type' => 1,
                'payment_method_id' => $request->payment_method_id, 
                'description' => $request->description ?? null,
                'amount' => $amount,
                'image' => $imagePath, 
                'created_by' => Auth::user()->id,
                'is_approved' => true,
                'approved_by' => Auth::user()->id
            ]);

            $paymentMethod->income_in_hand -= $amount;
            $paymentMethod->balance -= $amount;
            $paymentMethod->save(); 
            DB::commit(); 
            return success_response("জিম্মাদার হুজুরের কাছে টাকা জমা সফলভাবে সম্পন্ন হয়েছে।"); 
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage()); 
        }
    }


    public function depositList(Request $request)
    {
        $query = OfficeTransaction::with(['hijriMonth', 'paymentMethod'])
            ->where('type', 1)
            ->select('id', 'payment_method_id', 'description', 'amount', 'image','created_at');

        $perPage = $request->input('per_page', 10);  
        $page = $request->input('page', 1);  
        $total = $query->count();  

        $results = $query->skip(($page - 1) * $perPage)   
                        ->take($perPage)  
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'date' => app(HijriDateService::class)->getHijri($item->created_at),
                                'payment_method_icon' => $item->paymentMethod->icon,
                                'description' => $item->description,
                                'amount' => $item->amount,
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
    }


}
