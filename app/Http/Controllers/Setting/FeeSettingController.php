<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\FeeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeeSettingController extends Controller
{
    public function index()
    {
        $data = [
            'maktab_fee' => FeeSetting::where('key', 'maktab_fee')->first()->value ?? 0,
            'kitab_fee' => FeeSetting::where('key', 'kitab_fee')->first()->value ?? 0,
        ]; 
        return success_response($data);
    }

    public function store(Request $request)
    {
        $maktab_fee = $request->maktab_fee;
        $kitab_fee = $request->kitab_fee;  
        $authId = Auth::user()->id;
        $maktab = FeeSetting::where('key', 'maktab_fee')->first();
        if ($maktab) {
            $maktab->value = $maktab_fee;
            $maktab->updated_by = $authId;
            $maktab->save();
        } else {
            FeeSetting::create([
                'key' => 'maktab_fee',
                'value' => $maktab_fee,
                'created_by' => $authId
            ]);
        }

        $kitab = FeeSetting::where('key', 'kitab_fee')->first();
        if ($kitab) {
            $kitab->value = $kitab_fee;
            $maktab->updated_by = $authId;
            $kitab->save();
        } else {
            FeeSetting::create([
                'key' => 'kitab_fee',
                'value' => $kitab_fee,
                'created_by' => $authId
            ]);
        }

        return success_response(null, "Fees updated successfully");
    }

}
