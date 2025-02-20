<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\InterviewScheduleRequest;
use App\Models\AdmissionProgressStatus;
use App\Models\InterviewSchedule;
use App\Models\About;
use App\Models\StudentNote;
use App\Models\StudentRegister;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\PhoneMessageService;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Twilio\Rest\Client; 

class InterviewController extends Controller
{ 
    protected $messageService;

    public function __construct(PhoneMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function schedule(InterviewScheduleRequest $request) { 
        $interview_date = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->date . ' ' . ($request->time ?? '00:00')
        ); 
        
        try {
            $user = User::find($request->candidate_id);
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->first();   
            
            if(!$progress){
                return error_response('প্রার্থী পাওয়া যায়নি', 404); 
            }    
            if($progress->is_interview_scheduled){
                return error_response('ইন্টারভিউ শিডিউল ইতিমধ্যে পাঠানো হয়েছে');
            }  
    
            $student = StudentRegister::where('user_id',$request->candidate_id)->first();
            // if($student->department_id==1){
            //     $meetlink = About::where('keyword', 'maktab_meet_link')->first()->value??null;
            // }else{
            //     $meetlink = About::where('keyword', 'kitab_meet_link')->first()->value??null;
            // } 
            $message = $request->message;
            
            $schedule = new InterviewSchedule(); 
            $schedule->candidate_id = $request->candidate_id;
            $schedule->interviewer_id = $request->interviewer_id;
            $schedule->requested_at = $interview_date;  
            $schedule->notes = $message; 
            $schedule->save();   
            $progress->is_interview_scheduled = true;
            $progress->save();   
            // $message = "সম্মানিত অভিভাবক! আপনার তালিবে ইলমকে ইমতিহানের জন্য ( $request->custom_date এবং মিট লিঙ্ক:- $meetlink ) প্রস্তুত থাকার অনুরোধ করছি। ইমতিহানের সময় মাদ্রাসাতুল মাদিনার দরসের পোশাক ( অন্তত সাদা পোশাক ) পরে বসা কাম্য। অভিভাবকের উপস্থিতি আবশ্যক।";
            $response = $this->messageService->sendMessage($user->phone, $message);   
            return success_response(null, "সাক্ষাৎকারের শিডিউল সফলভাবে পাঠানো হয়েছে"); 
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

            // if($request->notes!=null){
            //     StudentNote::create([
            //         'employee_id' => Auth::user()->id,
            //         'student_id' => $request->candidate_id,
            //         'notes' => $request->notes,
            //     ]);
            // }

            DB::commit();
            return success_response(null, "Result Updated");
        } catch (\Exception $e) {
            DB::rollBack();  
            return error_response($e->getMessage(), 500);
        }
    }
}
