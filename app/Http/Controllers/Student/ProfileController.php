<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function profile($id = null)
    {
        try {
            if (!$id) {
                $id = Auth::user()->id;
            }
     
            $user = User::find($id);
    
            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।"); 
            }
    
            if (!$user->student) {
                return error_response(null, 404, "এই ইউজারের শিক্ষার্থী তথ্য পাওয়া যায়নি।"); 
            }
     
            $datas = [
                'name' => $user->name,
                'phone' => $user->phone,
                'profile_image' => $user->profile_image,
                'dob_hijri' => $user->dob_hijri,
                'blood_group' => $user->blood_group,
                'reg_id' => $user->student->reg_id,
                'jamaat' => $user->student->jamaat,
                'average_marks' => $user->student->average_marks,
            ];
    
            return success_response($datas);
        } catch (\Exception $e) {
            return error_response($e->getMessage()); 
        }
    } 

    public function PaymentHistory(Request $request, $id = null)
    {
        try {
            if (!$id) {
                $id = Auth::id();
            }

            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।");
            }

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1); 
            $query = Payment::where('user_id', $id); 
            $total = $query->count(); 
            $payments = $query->skip(($page - 1) * $perPage)
                            ->take($perPage)
                            ->get();

            $datas = [
                'month' => @$payments->hijriMonth->month." - ".@$payments->hijriMonth->year,
                'reason' => $payments->reason,
                'fee_type' => $payments->fee_type,
                'amount' => $payments->amount,
                'amount' => $payments->amount,
                "status" => $payments->due==0?"Paid":"Unpaid",
            ]; 

            return success_response([
                'data' => $datas,
                'pagination' => [
                    'total' => $total,
                    'per_page' => (int) $perPage,
                    'current_page' => (int) $page,
                    'last_page' => ceil($total / $perPage),
                ]
            ]);  

        } catch (\Exception $e) {
            return error_response(null, 500, $e->getMessage());
        }
    } 

    public function EnroleHistory($id){
            if (!$id) {
                $id = Auth::id();
            }

            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "ইউজার পাওয়া যায়নি।");
            } 
    }


    
}
