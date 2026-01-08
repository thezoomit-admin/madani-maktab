<?php

use App\Enums\FeeType;
use App\Helpers\ReportingService;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Student\AttendanceSyncController;
use App\Models\Admission;
use App\Models\EmployeeRole;
use App\Models\Enrole;
use App\Models\Expense;
use App\Models\HijriMonth;
use App\Models\OfficeTransaction;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Models\StudentRegister;
use App\Models\TeacherComment;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Models\ProductImage;
use App\Models\AnswerFile;
use App\Services\PhoneMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;
use Carbon\Carbon;

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

Route::get('/',function(){
     dd("Success");
});

// Route::get('update-reg-id', function(){ 
//      $startDate = Carbon::now()->subMonths(5); 
//      $endDate   = Carbon::now(); 
//      $students = StudentRegister::whereBetween('created_at', [$startDate, $endDate])->get();
//     foreach($students as $student){
//           $student->reg_id = null;
//                $student->save();
//     }

//     foreach($students as $student){
//           if($student->department_id==1){
//                $student->reg_id = StudentRegister::nextMaktabId();
//                $student->save();
//           }else{
//                $student->reg_id = StudentRegister::nextKitabId();
//                $student->save();
//           } 
//     }
//     return 'Reg IDs updated successfully!';
// });
 

// Route::get('/refresh', function () {  
   
//  DB::statement('SET FOREIGN_KEY_CHECKS=0;'); 
//      $payments = Payment::all();
//      foreach($payments as $payment){
//           $payment->paid = 0;
//           $payment->due = $payment->amount;
//           $payment->save();
//      }
//      PaymentTransaction::truncate(); 
//      $payments_methods = PaymentMethod::all();
//      foreach($payments_methods as $method){
//           $method->income_in_hand = 0;
//           $method->expense_in_hand = 0;
//           $method->balance = $method->income_in_hand; 
//           $method->save();
//      }
//      OfficeTransaction::truncate();
//      Expense::truncate();
//      DB::statement('SET FOREIGN_KEY_CHECKS=1;');
//      return 'Refresh completed successfully!';
// });
 
