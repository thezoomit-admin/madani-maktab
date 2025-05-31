<?php

namespace App\Http\Controllers\Report;

use App\Enums\ArabicMonth;
use App\Helpers\HijriDateService;
use App\Http\Controllers\Controller;
use App\Models\OfficeTransaction;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    public function collection(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return error_response(null, 422, 'ইনপুট ডেটা সঠিক নয়।');
        }

        DB::beginTransaction();
        try {
            $amount = $request->amount;
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request, 'image', 'uploads/transactions');
            }

            $paymentMethod = PaymentMethod::find($request->payment_method_id);

            OfficeTransaction::create([
                'type' => 2,
                'payment_method_id' => $request->payment_method_id, 
                'description' => $request->description ?? null,
                'amount' => $amount,
                'image' => $imagePath,
                'created_by' => Auth::user()->id,
                'is_approved' => true,
                'approved_by' => Auth::user()->id,
            ]);

            $paymentMethod->expense_in_hand += $amount;
            $paymentMethod->balance += $amount;
            $paymentMethod->save();

            DB::commit();
            return success_response(null, 'জিম্মাদার হুজুরের থেকে টাকা সংগ্রহ করা হয়েছে।');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500, 'টাকা সংগ্রহে ব্যর্থ হয়েছে।');
        }
    } 
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
    public function collectionList(Request $request)
    {
        $query = OfficeTransaction::with(['hijriMonth', 'paymentMethod'])
            ->where('type', 2)
            ->select('id', 'hijri_month_id', 'payment_method_id', 'description', 'amount', 'image','created_at');

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
                    'payment_method_icon' => $item->paymentMethod->icon ?? null,
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
        ], 'জমার তালিকা সফলভাবে পাওয়া গেছে।');
    }

}
