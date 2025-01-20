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
     $message = "test";
     return $messageService->sendMessage(+8801766774016, $message);
});
 
