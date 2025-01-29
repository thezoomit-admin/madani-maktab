<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Exception;

class MeetLinkSettingController extends Controller
{
    public function index(){
        try{
            $meetlink = About::where('keyword', 'meet_link')->first();
            return success_response($meetlink); 
        }catch(Exception $e){
            return success_response($e->getMessage()); 
        }
    }

    public function update(Request $request)
    {
        try {
            $meetlink = About::where('keyword', 'meet_link')->first();
            if (!$meetlink) {
                $meetlink = new About();
            }
            $meetlink->value = $request->link;
            $meetlink->save(); 
            return success_response('মিটিং লিঙ্ক আপডেট করা হয়েছে'); 
        } catch (Exception $e) { 
            return success_response($e->getMessage()); 
        }
    }
}
