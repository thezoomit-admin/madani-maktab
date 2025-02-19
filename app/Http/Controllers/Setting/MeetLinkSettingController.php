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
            $kitab_meet_link = About::where('keyword', 'kitab_meet_link')->first();
            $maktab_meet_link = About::where('keyword', 'maktab_meet_link')->first();

            $response = [
                [
                    "id" => 1,
                    "name" => "kitab_meet_link",
                    "meet_link" => $kitab_meet_link->value ?? "",
                ],
                [
                    "id" => 2,
                    "name" => "maktab_meet_link",
                    "meet_link" => $maktab_meet_link->value ?? "",
                ]
            ]; 
            return success_response($response);
            
        }catch(Exception $e){
            return success_response($e->getMessage()); 
        }
    }

    public function update(Request $request)
    {
        try {
            if(isset($request->kitab_meet_link) && $request->kitab_meet_link!=null){
                $kitab_meet_link = About::where('keyword', 'kitab_meet_link')->first();
                if (!$kitab_meet_link) {
                    $kitab_meet_link = new About();
                }
                $kitab_meet_link->keyword = "kitab_meet_link";
                $kitab_meet_link->value = $request->kitab_meet_link;
                $kitab_meet_link->save();
            }
              
            if(isset($request->maktab_meet_link) && $request->maktab_meet_link!=null){
                $maktab_meet_link = About::where('keyword', 'maktab_meet_link')->first();
                if (!$maktab_meet_link) {
                    $maktab_meet_link = new About();
                }
                $maktab_meet_link->keyword = "maktab_meet_link";
                $maktab_meet_link->value = $request->maktab_meet_link;
                $maktab_meet_link->save(); 
            }
            

            return success_response('মিটিং লিঙ্ক আপডেট করা হয়েছে'); 
        } catch (Exception $e) { 
            return success_response($e->getMessage()); 
        }
    }
}
