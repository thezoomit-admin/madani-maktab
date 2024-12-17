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
                return error_response( 'No districts found', 404);
            }  
            return success_response($data);   
        } catch (\Exception $e) {  
            return error_response($e->getMessage(),500);
        }
    }

}
