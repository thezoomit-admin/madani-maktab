<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Union;
use Illuminate\Http\Request;

class UnionApiController extends Controller
{
    public function __invoke(Request $request)
    {
        try {  
            $keyword = $request->input('keyword', ''); 
            $upazilaId = $request->input('upazila_id', null); 
 
            $data = Union::select('id', 'name')
                ->when($upazilaId, function ($query) use ($upazilaId) { 
                    return $query->where('upazila_id', $upazilaId);
                })
                ->where(function ($query) use ($keyword) {  
                    $query->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
 
            if ($data->isEmpty()) {
                return api_response(null, 'No union found', false, 404);
            }  
            return api_response($data);   
        } catch (\Exception $e) {  
            return api_response(null, 'An error occurred while fetching districts', false, 500, ['exception' => $e->getMessage()]);
        }
    }
}
