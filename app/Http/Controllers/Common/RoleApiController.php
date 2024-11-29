<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleApiController extends Controller
{
    public function __invoke()
    {
        try { 
            $data = Role::select('id','name','slug')->get(); 
            if ($data->isEmpty()) {
                return api_response(null, 'No roles found', false, 404);
            } 
            return api_response($data);
            
        } catch (\Exception $e) {   
            return api_response(null, 'An error occurred while fetching roles', false, 500, ['exception' => $e->getMessage()]);
        }
    }
}
