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
     $messageService = new PhoneMessageService;
     $message = "সম্মানিত অভিভাবক! আপনার তালিবে ইলমকে ইমতিহানের জন্য ( আপনার তালিবে ইলমকে ইমতিহানের মিট লিঙ্ক:- https://meet.google.com/gmq-zxxj-nhw ) প্রস্তুত থাকার অনুরোধ করছি। ইমতিহানের সময় মাদ্রাসাতুল মাদিনার দরসের পোশাক ( অন্তত সাদা পোশাক ) পরে বসা কাম্য। অভিভাবকের উপস্থিতি আবশ্যক।";
     return $messageService->sendMessage(+8801766774016, $message);
});
 
