<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\StudentRegister; 
use App\Services\MessageService;
use App\Services\PhoneMessageService;
use Illuminate\Http\Request;
use Exception;

class SendRegistrationNumberController extends Controller
{
    protected $messageService;

    public function __construct(PhoneMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function __invoke(Request $request)
    { 
        // Validate incoming request data
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id', 
            'message' => 'required|string|max:1600',
            'message_type' => 'nullable|string|in:fail_message,step_2_link,final_pass',
        ]);
        
        // Find student registration based on the user_id
        $student_register = StudentRegister::where('user_id', $validated['user_id'])->first();
    
        if ($student_register) {
            $phone_number = $student_register->user->phone;
            $message = $validated['message'];
            $message_type = $validated['message_type'];
            try { 
                $response = $this->messageService->sendMessage($phone_number, $message); 
                $admission_progress = AdmissionProgressStatus::where('user_id', $student_register->user_id)->first();
                if($message_type=="fail_message"){
                    $admission_progress->is_send_fail_message = 1;
                }elseif($message_type=="step_2_link"){
                    $admission_progress->is_send_step_2_link = 1;
                }elseif($message_type=="final_pass"){
                    $admission_progress->is_send_final_pass_message = 1;
                } 
                $admission_progress->save();  
                return success_response('Message sent successfully!');

            } catch (Exception $e) {  
                return error_response($e->getMessage(), 500);
            }

        } else {  
            return error_response('Invalid Registration Number', 404);
        }
    }
}
