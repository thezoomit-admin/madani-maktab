<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\InterviewScheduleRequest;
use App\Models\AdmissionProgressStatus;
use App\Models\InterviewSchedule;
use Exception;
use Illuminate\Http\Request;

class InterviewController extends Controller
{ 
    public function store(InterviewScheduleRequest $request){ 
        $interview_date = $request->date . ' ' . $request->time; 
        try {
            $progress = AdmissionProgressStatus::where('user_id',$request->candidate_id)->first();
            if(!$progress){
                error_response('Candidate not found');
            }
            
            $ex_candidate = InterviewSchedule::where('candidate_id',$request->candidate_id)->first();
            if($ex_candidate){
                $schedule = $ex_candidate;
            }else{
                $schedule = new InterviewSchedule();
            }

            $schedule->candidate_id = $request->candidate_id;
            $schedule->interviewer_id = $request->interviewer_id;
            $schedule->interview_date = $interview_date;
            $schedule->location = $request->location ?? 'online';
            $schedule->notes = $request->notes; 
            $schedule->save();  
            
            $progress->is_interview_scheduled = true;
            $progress->save();
            return success_response(null,"Schedule Created"); 
        } catch (Exception $e) { 
            return error_response($e->getMessage(), 500);
        }
    } 

    public function result(){

    }
}
