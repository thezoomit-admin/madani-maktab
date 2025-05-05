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
        $total = $payment_methods->sum('balance'); 
        return success_response([
            'payment_methods' => $payment_methods,
            'total' => $total,
        ]);
    }  

    public function expenseBalance(){
        $payment_methods = PaymentMethod::select('id','icon','name','expense_in_hand as balance')->get();
        $total = $payment_methods->sum('balance'); 
        return success_response([
            'payment_methods' => $payment_methods,
            'total' => $total,
        ]);
    }
}
