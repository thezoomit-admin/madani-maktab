<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationApiController extends Controller
{
    public function __invoke()
    {
        try { 
            $data = Designation::select('id','title','slug')->get(); 
            if ($data->isEmpty()) {
                return api_response(null, 'No designation found', false, 404);
            } 
            return api_response($data);
            
        } catch (\Exception $e) {   
            return api_response(null, 'An error occurred while fetching designations', false, 500, ['exception' => $e->getMessage()]);
        }
    }
}
