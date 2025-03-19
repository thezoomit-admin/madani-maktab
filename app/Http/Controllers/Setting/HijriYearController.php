<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\HijriYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HijriYearController extends Controller
{
    public function index()
    {
        $hijriYears = HijriYear::all();
        return success_response($hijriYears); 
    }
 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|string|max:4',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return error_response(null,422,$validator->errors()); 
        }  
       HijriYear::create([
            'year' => $request->year, 
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]); 
        return success_response('Hijri Year created successfully'); 
    }  
 
    public function update(Request $request, $id)
    {
        $hijriYear = HijriYear::find($id);

        if (!$hijriYear) { 
            return error_response(null,422,"Hijri Year not found");  
        }

        $validator = Validator::make($request->all(), [
            'year' => 'string|max:4', 
            'start_date' => 'date',
            'end_date' => 'nullable|date'
        ]);

        if ($validator->fails()) { 
            return error_response(null,422, $validator->errors());  
        }

        $hijriYear->update([
            'year' => $request->year ?? $hijriYear->year, 
            'start_date' => $request->start_date ?? $hijriYear->start_date,
            'end_date' => $request->end_date ?? $hijriYear->end_date,
        ]); 
        return success_response('Hijri Year updated successfully');  
    } 
}
