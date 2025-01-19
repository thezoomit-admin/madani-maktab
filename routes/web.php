<?php

use App\Helpers\ReportingService;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Models\StudentRegister;
use App\Models\User;
use App\Services\PhoneMessageService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {     
    $student_register = StudentRegister::where('user_id', 215)->first();
        if ($student_register) {
            $phone_number = $student_register->user->phone;
            $message = "সম্মানিত অভিভাবক! আলহামদুলিল্লাহ, আপনার দেয়া তথ্য অনুযায়ী আপনার সন্তান: {$student_register->name} নিবন্ধন নাম্বার: {$student_register->reg_id} পরবর্তী ধাপের জন্য নির্বাচিত হয়েছে।
                        আপনার সন্তানের দ্বীনী ইলম হাছিলের এ মহান সাধনায় আপনার এবং আপনার ঘরের মন মানসিকতা ও পরিবেশের রয়েছে অপরিসীম ভূমিকা। তাই এ বিষয়েও আমরা আপনার কাছে কিছু জানতে চাই। নিচের ঠিকানায় প্রবেশ করে সুচিন্তিতভাবে উত্তরগুলো লিখে পাঠান। আল্লাহ তাওফিক দান করুন https://admission.mimalmadinah.com/admission-form/step-3?user_id={$student_register->user_id}";

            try {
                $message_service = new PhoneMessageService;
                return $message_service->sendMessage($phone_number, $message); 
            } catch (Exception $e) {
                return error_response( $e->getMessage(), 500); 
            }
        } else {
            return error_response('Invalid Registration Number', 404);
        }
});

Route::get("sendMessage",[InterviewController::class,'sendSms']);
