<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Upazila;
use Illuminate\Http\Request;

class UpazilaApiController extends Controller
{
    public function __invoke(Request $request)
    {
        try {  
            $keyword = $request->input('keyword', ''); 
            $districtId = $request->input('district_id', null); 
 
            $data = Upazila::select('id', 'name')
                ->when($districtId, function ($query) use ($districtId) { 
                    return $query->where('district_id', $districtId);
                })
                ->where(function ($query) use ($keyword) {  
                    $query->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
 
            if ($data->isEmpty()) {
                return api_response(null, 'No upazila found', false, 404);
            }  
            return api_response($data);   
        } catch (\Exception $e) {  
            return api_response(null, 'An error occurred while fetching districts', false, 500, ['exception' => $e->getMessage()]);
        }
    }
}
