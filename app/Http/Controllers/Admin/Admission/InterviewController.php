<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\InterviewScheduleRequest;
use App\Models\AdmissionProgressStatus;
use App\Models\InterviewSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InterviewController extends Controller
{ 
    public function store(InterviewScheduleRequest $request){ 
        $interview_date = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
        try {
            $progress = AdmissionProgressStatus::where('user_id',$request->candidate_id)->first();
            if(!$progress){
                return error_response('Candidate not found',404);
            }
            
            $ex_candidate = InterviewSchedule::where('candidate_id',$request->candidate_id)->first();

            if($ex_candidate){
                $schedule = $ex_candidate;
            }else{
                $schedule = new InterviewSchedule();
            }

            $schedule->candidate_id = $request->candidate_id;
            $schedule->interviewer_id = $request->interviewer_id;
            $schedule->requested_at = $interview_date;
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

    public function result(Request $request)
    { 
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|exists:interview_schedules,candidate_id',
            'notes'        => 'nullable|string|max:1000',
            'result'       => 'required|boolean',
        ]); 
        if ($validator->fails()) {
            return error_response($validator->errors()->first(), 422);
        }

        DB::beginTransaction();
        try { 
            $interview = InterviewSchedule::where('candidate_id', $request->candidate_id)->firstOrFail();
            $interview->update([
                'notes'  => $request->notes,
                'attended_at' => now(),
                'status' => 'completed',
            ]);
 
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->firstOrFail();
            $progress->update([
                'is_passed_interview' => $request->result,
            ]);

            DB::commit();
            return success_response(null, "Result Updated");
        } catch (\Exception $e) {
            DB::rollBack();  
            return error_response($e->getMessage(), 500);
        }
    }
}
