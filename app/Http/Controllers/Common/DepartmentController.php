<?php

namespace App\Http\Controllers\Common;

use App\Enums\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(){
        $values = Department::values(); 
        $list = [];
        foreach ($values as $key => $name) {
            $list[] = [
                'id' => (int) $key,
                'name' => $name,
            ];
        }
        return $list;
    } 
}
