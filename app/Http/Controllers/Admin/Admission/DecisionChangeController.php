<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\StudentRegister;
use Illuminate\Http\Request;

class DecisionChangeController extends Controller
{
    public function notInterested(Request $request){
        $candidate_id = $request->candidate_id;
        $progress = AdmissionProgressStatus::where('user_id', $candidate_id)->first();
        if(!$progress){
            return error_response(null, 404, 'ডেটা পাওয়া যায়নি।');
        }
        $progress->is_interested = false;
        $progress->save();

        $student = StudentRegister::where('user_id', $candidate_id)->first();
        if($student){
            $student->note = $student->note." আগ্রহ প্রকাশ করেনি: ".$request->note;
            $student->save();
        }
        return success_response(null, 'আপডেট সফল হয়েছে।');
    }
    public function interested(Request $request){
        $candidate_id = $request->candidate_id;
        $progress = AdmissionProgressStatus::where('user_id', $candidate_id)->first();
        if(!$progress){
            return error_response(null, 404, 'ডেটা পাওয়া যায়নি।');
        }
        $progress->is_interested = true;
        $progress->save();

        $student = StudentRegister::where('user_id', $candidate_id)->first();
        if($student){
            $student->note = $student->note." আগ্রহ প্রকাশ করেছে: ".$request->note;
            $student->save();
        }
        return success_response(null, 'আপডেট সফল হয়েছে।');
    }
}
