<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CountryApiController extends Controller
{
    public function __invoke(Request $request)
    { 
        try { 
            $keyword = $request->input('keyword', ''); 
            $data = Country::select('id', 'name', 'nationality')
                ->where(function ($query) use ($keyword) { 
                    $query->where('name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nationality', 'LIKE', '%' . $keyword . '%');
                })
                ->take(10)
                ->get();  
            if ($data->isEmpty()) {
                return error_response('No countries found', 404);
            } 
            return success_response($data);  
            
        } catch (\Exception $e) { 
            return error_response($e->getMessage(),500);
        }
    }

}
