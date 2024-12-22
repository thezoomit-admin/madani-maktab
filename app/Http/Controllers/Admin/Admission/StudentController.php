<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function student($id){
        try{
            $user = User::with('studentRegister')
            ->with('address')
            ->with('guardian')->find($id);
            return success_response($user);
        }catch(Exception $e){
            return error_response($e->getMessage(),500);
        }
    }
}
