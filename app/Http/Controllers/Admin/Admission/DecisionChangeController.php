<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DecisionChangeController extends Controller
{
    public function notInterested(Request $request){
        $candidate_id = $request->candidate_id;
        $progress = AdmissionProgressStatus::where('candidate_id', $candidate_id)->first();
        if($progress){
            $progress->is_passed_age = false;
            $progress->save();
        }
        $student = StudentRegister::where('user_id', $candidate_id)->first();
        if($student){
            $student->note = $student->note." আগ্রহ প্রকাশ করেনি: ".$request->note;
            $student->save();
        }
    }
    public function interested(Request $request){
        $candidate_id = $request->candidate_id;
        $progress = AdmissionProgressStatus::where('candidate_id', $candidate_id)->first();
        if($progress){
            $progress->is_passed_age = true;
            $progress->save();
        }
        $student = StudentRegister::where('user_id', $candidate_id)->first();
        if($student){
            $student->note = $student->note." আগ্রহ প্রকাশ করেছে: ".$request->note;
            $student->save();
        }
    }
}
