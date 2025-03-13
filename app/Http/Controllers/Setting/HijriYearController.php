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
            'is_current' => 'boolean',
            'start_date' => 'required|integer',
            'end_date' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $oldActive = HijriYear::where('is_current',1)->get()->update(['is_current'=> 0]);
        $hijriYear = HijriYear::create([
            'year' => $request->year, 
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        return response()->json([
            'message' => 'Hijri Year created successfully',
            'data' => $hijriYear
        ], 201);
    }

    /**
     * Display the specified Hijri year.
     *
     * @param  \App\Models\HijriYear  $hijriYear
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $hijriYear = HijriYear::find($id);

        if (!$hijriYear) {
            return response()->json([
                'message' => 'Hijri Year not found'
            ], 404);
        }

        return response()->json([
            'data' => $hijriYear
        ]);
    }

    /**
     * Update the specified Hijri year in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HijriYear  $hijriYear
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $hijriYear = HijriYear::find($id);

        if (!$hijriYear) {
            return response()->json([
                'message' => 'Hijri Year not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'year' => 'string|max:4',
            'is_current' => 'boolean',
            'start_date' => 'integer',
            'end_date' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $hijriYear->update([
            'year' => $request->year ?? $hijriYear->year,
            'is_current' => $request->is_current ?? $hijriYear->is_current,
            'start_date' => $request->start_date ?? $hijriYear->start_date,
            'end_date' => $request->end_date ?? $hijriYear->end_date,
        ]);

        return response()->json([
            'message' => 'Hijri Year updated successfully',
            'data' => $hijriYear
        ]);
    }

    /**
     * Remove the specified Hijri year from storage.
     *
     * @param  \App\Models\HijriYear  $hijriYear
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $hijriYear = HijriYear::find($id);

        if (!$hijriYear) {
            return response()->json([
                'message' => 'Hijri Year not found'
            ], 404);
        }

        $hijriYear->delete();

        return response()->json([
            'message' => 'Hijri Year deleted successfully'
        ]);
    }
}
