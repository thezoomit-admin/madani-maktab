<?php

namespace App\Http\Controllers\Admin\Admission;

use App\Http\Controllers\Controller;
use App\Models\AdmissionProgressStatus;
use App\Models\StudentRegister;
use App\Services\MessageService;
use App\Services\PhoneMessageService;
use Exception;

class SendRegistrationNumberController extends Controller
{
    protected $messageService;

    public function __construct(PhoneMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function __invoke($user_id)
    {
        $student_register = StudentRegister::where('user_id', $user_id)->first(); 
        if ($student_register) {
            $phone_number = $student_register->user->phone;
            $message = "সম্মানিত অভিভাবক, আলহামদুলিল্লাহ, তালিবে ইলম: {$student_register->name} নিবন্ধন নং: {$student_register->reg_id} পরবর্তী ধাপের জন্য নির্বাচিত হয়েছে। আশা করি নিচের লিংকে প্রবেশ করে গুরুত্বপূর্ণ কিছু প্রশ্নের উত্তর দিবেন৷ https://admission.mimalmadinah.com/admission-form/step-3?user_id={$student_register->user_id}";

            try {
                $response = $this->messageService->sendMessage($phone_number, $message);
                $status = AdmissionProgressStatus::where('user_id', $student_register->user_id)->first();
                $status->is_registration_complete = 0;
                $status->save();
                return success_response('Message sent successfully!'); 
            } catch (Exception $e) {
                return error_response( $e->getMessage(), 500); 
            }
        } else {
            return error_response('Invalid Registration Number', 404);
        }
    }
}
