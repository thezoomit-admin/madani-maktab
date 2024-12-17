<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionApiController extends Controller
{
    public function __invoke(Request $request)
    {
        try {  
            $keyword = $request->input('keyword', ''); 
            $countryId = $request->input('country_id', null); 
 
            $data = Division::select('id', 'name')
                ->when($countryId, function ($query) use ($countryId) { 
                    return $query->where('country_id', $countryId);
                })
                ->where(function ($query) use ($keyword) {  
                    $query->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
 
            if ($data->isEmpty()) {
                return error_response('No divison found',404);
            }  
            return success_response($data);   
        } catch (\Exception $e) {  
            return error_response($e->getMessage(),500);
        }
    }
}
