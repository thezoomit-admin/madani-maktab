<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use Illuminate\Http\Request;

class ProfilePrintStatusController extends Controller
{
    public function updateStatus(Request $request){
        $admission_progress = AdmissionProgressStatus::where('user_id', $request->user_id)->first();
        if(!$admission_progress){
            return error_response(null,404,"User not found");
        } 

        $admission_progress->is_print_profile = true;
        $admission_progress->save();
        return success_response(null, "Print status updated");
    }
}
