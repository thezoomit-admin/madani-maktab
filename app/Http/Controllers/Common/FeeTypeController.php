<?php

namespace App\Http\Controllers\Common;
use App\Http\Controllers\Controller;
use App\Enums\FeeType;  

class FeeTypeController extends Controller
{
    public function feeList(){
        $values = FeeType::values();

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
