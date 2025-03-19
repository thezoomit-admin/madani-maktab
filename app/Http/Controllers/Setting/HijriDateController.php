<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\HijriDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HijriDateController extends Controller
{
    public function index(){
        $datas = HijriDate::with(['year','month'])->latest()->get(); 
        $months = $datas->map(function($data){
            return [
                'id' => $data->id,
                'year_id' => $data->hijri_year_id, 
                'year' => $data->year->year,
                'month_id' => $data->hijri_month_id,
                'month' => $data->month->month,
                'start_date' => $data->start_date,
                'end_date' => $data->end_date??"Running",
            ];
        });
        return success_response($months);
    } 

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hijri_year_id' => 'required|exists:hijri_years,id',
            'hijri_month_id' => 'required|exists:hijri_months,id',
            'start_date' => 'required|date_format:Y-m-d',
        ]);  

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }

        $is_existing = HijriDate::where('hijri_year_id', $request->hijri_year_id)
            ->where('hijri_month_id', $request->hijri_month_id)
            ->exists(); 

        if ($is_existing) {
            return error_response(null, 400, "This Date is already created");
        }


 
        $lastDate = HijriDate::latest()->first(); 
        if ($lastDate) { 
            $lastDate->end_date = Carbon::parse($request->start_date)->subDay()->format('Y-m-d');
            $lastDate->save();
        } 
        HijriDate::create($request->all()); 
        return success_response(null, "Hijri Date Created Successfully");
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'hijri_year_id' => 'required|exists:hijri_years,id',
            'hijri_month_id' => 'required|exists:hijri_months,id',
            'start_date' => 'required|date_format:Y-m-d',
        ]);  

        if ($validator->fails()) {
            return error_response(null, 422, $validator->errors());
        }  

        $hijriDate = HijriDate::find($id); 
        if (!$hijriDate) {
            return error_response(null, 404, "Hijri Date not found");
        } 
        $previousDate = HijriDate::where('id', '<', $id)->latest()->first(); 
        if ($previousDate) { 
            $previousDate->end_date = Carbon::parse($request->start_date)->subDay()->format('Y-m-d');
            $previousDate->save();
        } 
        $hijriDate->update($request->all()); 
        return success_response(null, "Hijri Date Updated Successfully");
    } 
}
