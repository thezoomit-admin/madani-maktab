<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\HijriDate;
use App\Models\HijriMonth;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index(Request $request)
    { 
        $vendors = Vendor::all();
        return success_response($vendors);    
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        Vendor::create($request->only('name', 'contact_person', 'phone'));
        return success_response(null, 'Vendor created successfully.');
    }

    public function show($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return error_response(null, 404, 'Vendor not found.');
        }

        return success_response($vendor, 'Vendor fetched successfully.');
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return error_response(null, 404, 'Vendor not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors(), 422, 'Validation failed.');
        }

        $vendor->update($request->only('name', 'contact_person', 'phone'));
        return success_response(null, 'Vendor updated successfully.');
    }

    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return error_response(null, 404, 'Vendor not found.');
        }

        $vendor->delete();
        return success_response(null, 'Vendor deleted successfully.');
    }
}
