<?php

use App\Helpers\ReportingService;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Models\Admission;
use App\Models\Enrole;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\User;
use App\Services\PhoneMessageService;
use Illuminate\Support\Facades\DB;
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


Route::get('/refresh', function () {
     // $messageService = new PhoneMessageService;
     // $message = "Test";
     // return $messageService->sendMessage(+8801766774016, $message); 
     DB::statement('SET FOREIGN_KEY_CHECKS=0;');

     // Update operations
     Admission::where('status', 1)->update(['status' => 0]);
     User::whereNotNull('reg_id')->update(['reg_id' => null]);
 
     // Truncate tables
     Student::truncate();
     Enrole::truncate();
     Payment::truncate();
     PaymentTransaction::truncate();
 
     DB::statement('SET FOREIGN_KEY_CHECKS=1;');
 
     return 'Refresh completed successfully!';
});
 
