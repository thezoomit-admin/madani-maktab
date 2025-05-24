<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\MeasurmentUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MeasurmentUnitController extends Controller
{
    public function index()
    {
        $units = MeasurmentUnit::all();
        return success_response($units, 'Measurement units fetched successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        $unit = MeasurmentUnit::create($request->only('name', 'short_name'));
        return success_response($unit, 'Measurement unit created successfully.');
    }

    public function show($id)
    {
        $unit = MeasurmentUnit::find($id);

        if (!$unit) {
            return error_response(null, 404, 'Measurement unit not found.');
        }

        return success_response($unit, 'Measurement unit fetched successfully.');
    }

    public function update(Request $request, $id)
    {
        $unit = MeasurmentUnit::find($id);

        if (!$unit) {
            return error_response(null, 404, 'Measurement unit not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'short_name' => 'sometimes|required|string|max:50',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        $unit->update($request->only('name', 'short_name'));
        return success_response($unit, 'Measurement unit updated successfully.');
    }

    public function destroy($id)
    {
        $unit = MeasurmentUnit::find($id);

        if (!$unit) {
            return error_response(null, 404, 'Measurement unit not found.');
        }

        $unit->delete();
        return success_response(null, 'Measurement unit deleted successfully.');
    }
}
