<?php

namespace App\Http\Controllers\Common;
use App\Http\Controllers\Controller;
use App\Enums\KitabSession;
use App\Enums\MaktabSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function maktabSession(){
        $values = MaktabSession::values();

        $list = [];
        foreach ($values as $key => $name) {
            $list[] = [
                'id' => (int) $key,
                'name' => $name,
            ];
        }

        return $list;
    }

    public function KitabSession(){
        $values = KitabSession::values();

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
