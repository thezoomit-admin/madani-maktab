<?php

use App\Enums\FeeType;
use App\Helpers\ReportingService;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Student\AttendanceSyncController;
use App\Models\Admission;
use App\Models\Enrole;
use App\Models\Expense;
use App\Models\OfficeTransaction;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\TeacherComment;
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


Route::get('/sync-attendance', [AttendanceSyncController::class, 'sync']);


Route::get('/refresh', function () {  
   
     DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 
     // Admission::where('status', 1)->update(['status' => 0]);
     // User::whereNotNull('reg_id')->update(['reg_id' => null]);
  
     // Student::truncate();
     // Enrole::truncate();
     // TeacherComment::truncate();
     // Payment::truncate();
     PaymentTransaction::truncate();
     
     $payments_methods = PaymentMethod::all();
     foreach($payments_methods as $method){
          $method->income_in_hand = 0;
          $method->expense_in_hand = 0;
          $method->balance = 0; 
          $method->save();
     }
     OfficeTransaction::truncate();
     Expense::truncate();
     DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     return 'Refresh completed successfully!';
});
 
