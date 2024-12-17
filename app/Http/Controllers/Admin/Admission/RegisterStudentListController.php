<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterStudentListController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = User::where('user_type','student')
        ->whereHas('studentRegister',function($q) use($request){
            $q->where('department_id',$request->department);
        })
        ->with('studentRegister')
        ->with('address')
        ->with('guardian')
        ->get();
        return success_response($data);
    }
}
