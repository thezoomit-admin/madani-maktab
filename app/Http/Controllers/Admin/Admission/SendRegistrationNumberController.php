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
            $message = "সম্মানিত অভিভাবক! আলহামদুলিল্লাহ, আপনার দেয়া তথ্য অনুযায়ী আপনার সন্তান: {$student_register->name} নিবন্ধন নাম্বার: {$student_register->reg_id} পরবর্তী ধাপের জন্য নির্বাচিত হয়েছে।
                        আপনার সন্তানের দ্বীনী ইলম হাছিলের এ মহান সাধনায় আপনার এবং আপনার ঘরের মন মানসিকতা ও পরিবেশের রয়েছে অপরিসীম ভূমিকা। তাই এ বিষয়েও আমরা আপনার কাছে কিছু জানতে চাই। নিচের ঠিকানায় প্রবেশ করে সুচিন্তিতভাবে উত্তরগুলো লিখে পাঠান। আল্লাহ তাওফিক দান করুন https://admission.mimalmadinah.com/admission-form/step-3?user_id={$student_register->user_id}";

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
