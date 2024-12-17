<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\CompanyCategory;
use Illuminate\Http\Request;

class CompanyCategoryApiController extends Controller
{
    public function __invoke(Request $request)
    {
        try { 
            $keyword = $request->input('keyword', ''); 
            $data = CompanyCategory::select('id', 'name', 'slug')
                ->where(function ($query) use ($keyword) { 
                    $query->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
            if ($data->isEmpty()) {
                return error_response('No category found', 404);
            } 
            return success_response($data);  
            
        } catch (\Exception $e) { 
            return error_response($e->getMessage(). 500);
        }
    }
}
