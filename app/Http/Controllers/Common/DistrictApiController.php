<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictApiController extends Controller
{
    public function __invoke(Request $request)
    {
        try {  
            $keyword = $request->input('keyword', ''); 
            $divisionId = $request->input('division_id', null); 
 
            $data = District::select('id', 'name')
                ->when($divisionId, function ($query) use ($divisionId) { 
                    return $query->where('division_id', $divisionId);
                })
                ->where(function ($query) use ($keyword) {  
                    $query->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
 
            if ($data->isEmpty()) {
                return api_response(null, 'No districts found', false, 404);
            }  
            return api_response($data);   
        } catch (\Exception $e) {  
            return api_response(null, 'An error occurred while fetching districts', false, 500, ['exception' => $e->getMessage()]);
        }
    }

}
