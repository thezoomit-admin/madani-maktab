<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeeSettingController extends Controller
{
    public function index()
    {
        $data = [
            'maktab_monthly_fee' => FeeSetting::where('key', 'maktab_monthly_fee')->value('value') ?? 0,
            'maktab_admission_fee' => FeeSetting::where('key', 'maktab_admission_fee')->value('value') ?? 0,
            'kitab_monthly_fee' => FeeSetting::where('key', 'kitab_monthly_fee')->value('value') ?? 0,
            'kitab_admission_fee' => FeeSetting::where('key', 'kitab_admission_fee')->value('value') ?? 0,
        ]; 

        return success_response($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'maktab_monthly_fee' => 'required|numeric|min:0',
            'maktab_admission_fee' => 'required|numeric|min:0',
            'kitab_monthly_fee' => 'required|numeric|min:0',
            'kitab_admission_fee' => 'required|numeric|min:0',
        ]);  

        try {
            DB::beginTransaction();

            $authId = Auth::id();
 
            FeeSetting::updateOrCreate(
                ['key' => 'maktab_fee'],
                [
                    'value' => $request->maktab_fee,
                    'created_by' => $authId,
                    'updated_by' => $authId
                ]
            );
 
            FeeSetting::updateOrCreate(
                ['key' => 'kitab_fee'],
                [
                    'value' => $request->kitab_fee,
                    'created_by' => $authId,
                    'updated_by' => $authId
                ]
            );

            DB::commit();

            return success_response(null, "Fees updated successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            return error_response("Something went wrong: " . $e->getMessage());
        }
    }

}
