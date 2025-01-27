<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\StudentRegister;
use App\Models\MessageStatus;
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
            'message_type' => 'required|string|in:fail_message,general_pass_message,interview_pass_message,final_pass_message', // Added validation for message_type
        ]);
        
        // Find student registration based on the user_id
        $student_register = StudentRegister::where('user_id', $validated['user_id'])->first();
    
        if ($student_register) { 
            $phone_number = $student_register->user->phone; 
            $message = $validated['message']; 
            
            try { 
                $response = $this->messageService->sendMessage($phone_number, $message);
 
                $status = AdmissionProgressStatus::where('user_id', $student_register->user_id)->first();
 
                $message_status = MessageStatus::where('user_id', $student_register->user_id)->first();
                if (!$message_status) {
                    $message_status = new MessageStatus();
                    $message_status->user_id = $student_register->user_id;  
                }  
                
                if ($validated['message_type'] == 'fail_message') {
                    $message_status->is_send_fail_message = 1;
                } elseif ($validated['message_type'] == 'general_pass_message') {
                    $message_status->is_send_general_pass_message = 1;
                } elseif ($validated['message_type'] == 'interview_pass_message') {
                    $message_status->is_send_interview_pass_message = 1;
                } elseif ($validated['message_type'] == 'final_pass_message') {
                    $message_status->is_send_final_pass_message = 1;
                } 

                // Save the updated message status
                $message_status->save(); 

                // Return success response
                return success_response('Message sent successfully!');

            } catch (Exception $e) { 
                // Handle exception and return error response
                return error_response($e->getMessage(), 500);
            }

        } else {  
            return error_response('Invalid Registration Number', 404);
        }
    }
}
