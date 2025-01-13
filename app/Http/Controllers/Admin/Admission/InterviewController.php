<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admission\InterviewScheduleRequest;
use App\Models\AdmissionProgressStatus;
use App\Models\InterviewSchedule;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Http; 
use Twilio\Rest\Client; 

class InterviewController extends Controller
{ 
    public function store(InterviewScheduleRequest $request){ 
        $interview_date = Carbon::createFromFormat('Y-m-d H:i', $request->date . ' ' . $request->time);
        
        try {
            $progress = AdmissionProgressStatus::where('user_id', $request->candidate_id)->first();  
            
            if(!$progress){
                return error_response('Candidate not found', 404);
            }   
            $ex_candidate = InterviewSchedule::where('candidate_id', $request->candidate_id)->first();
   
            if($ex_candidate){
                $schedule = $ex_candidate;
            } else {
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
            $phone = User::find($request->candidate_id)->phone; 
            $this->sendSms($phone, "আপনার ইন্টারভিউ " . $interview_date->format('Y-m-d H:i') . " তারিখে নির্ধারিত হয়েছে।");


            // $this->sendWhatsAppMessage($phone, "Your interview has been scheduled for " . $interview_date->format('Y-m-d H:i'));
            // $this->createGoogleMeetEvent($interview_date);
            return success_response(null, "Schedule Created"); 
        } catch (Exception $e) { 
            return error_response($e->getMessage(), 500);
        }
    }

    public function sendSms($phone, $message)
    {
        $apiUrl = env('AJURATECH_BASE_URL');
        $apiKey = env('AJURATECH_API_KEY');
        $secretKey = env('AJURATECH_SECRET_KEY');
        $senderId = env('AJURATECH_SENDER_ID');
     
        $queryParams = [
            'apikey' => $apiKey,
            'secretkey' => $secretKey,
            'callerID' => $senderId,
            'toUser' => $phone,
            'messageContent' => $message,
        ];
     
        $url = $apiUrl . '?' . http_build_query($queryParams);
    
        try { 
            $response = Http::get($url);  

            if ($response->successful()) {
                return $response->body();
            } else {
                throw new Exception('SMS sending failed. Status code: ' . $response->status());
                return false;
            }
        } catch (Exception $e) { 
            return false;
        }
    }
    
   
    // Function to send WhatsApp message
    private function sendWhatsAppMessage($phone, $message)
    {
        $sid = env('TWILIO_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_NUMBER');
        
       
        $client = new Client($sid, $auth_token);
        
        try {
            // Send the WhatsApp message
            $client->messages->create(
                'whatsapp:'.$phone,  
                [
                    'from' => $from,   
                    'body' => $message     
                ]
            );

            // Return a success message if the message is sent successfully
            return response()->json([
                'status' => 'success',
                'message' => 'WhatsApp message sent successfully!'
            ]);

        } catch (Exception $e) {
            // Handle the error if the message fails to send
            \Log::error('Twilio WhatsApp Error: ' . $e->getMessage());

            // Return a failure message
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send WhatsApp message. ' . $e->getMessage()
            ], 500);  // 500 is the HTTP status code for server errors
        }
    } 

  
    private function createGoogleMeetEvent($date)
    { 
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials/your-service-account-file.json'));
        $client->addScope(Google_Service_Calendar::CALENDAR);
 
        $service = new Google_Service_Calendar($client);
 
        $event = new Google_Service_Calendar_Event([
            'summary' => 'Interview with Candidate',
            'start' => [
                'dateTime' => Carbon::parse($date)->toRfc3339String(),
                'timeZone' => 'Asia/Dhaka',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
        ]);
    
        $event = $service->events->insert('primary', $event, ['conferenceDataVersion' => 1]);
    
        $googleMeetLink = $event->getConferenceData()->getEntryPoints()[0]->getUri();

        return $googleMeetLink;
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
