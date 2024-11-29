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
                return api_response(null, 'No divison found', false, 404);
            }  
            return api_response($data);   
        } catch (\Exception $e) {  
            return api_response(null, 'An error occurred while fetching districts', false, 500, ['exception' => $e->getMessage()]);
        }
    }
}
