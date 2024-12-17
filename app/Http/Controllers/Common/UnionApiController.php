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
                return error_response('No union found', 404);
            }  
            return success_response($data);   
        } catch (\Exception $e) {  
            return error_response($e->getMessage(),500);
        }
    }
}
