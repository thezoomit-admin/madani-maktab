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
 

Route::get('/refresh', function () {  
   
 DB::statement('SET FOREIGN_KEY_CHECKS=0;'); 
     $payments = Payment::all();
     foreach($payments as $payment){
          $payment->paid = 0;
          $payment->due = $payment->amount;
          $payment->save();
     }
     PaymentTransaction::truncate(); 
     $payments_methods = PaymentMethod::all();
     foreach($payments_methods as $method){
          $method->income_in_hand = 0;
          $method->expense_in_hand = 0;
          $method->balance = $method->income_in_hand; 
          $method->save();
     }
     OfficeTransaction::truncate();
     Expense::truncate();
     DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     return 'Refresh completed successfully!';
});
 
Route::get('/revert-last-step', function() {
    $reg_ids = ['ম-121', 'ম-119', 'ম-114', 'ম-111', 'ম-101', 'ম-100', 'ম-099', 'ম-094', 'ম-092', 'ম-091', 'ম-090', 'ম-084', 'ম-083', 'ম-082', 'ম-081', 'ম-080', 'ম-075', 'ম-074', 'ম-069', 'ম-065', 'ম-059', 'ম-058', 'ম-034', 'ম-028', 'ম-053', 'ম-014'];

    DB::beginTransaction();
    try {
        foreach($reg_ids as $id){
            $student = StudentRegister::where('reg_id', $id)->first();
            if($student){
                $user = $student->user;
                
                if($user->answerFiles && count($user->answerFiles)>0){
                    foreach($user->answerFiles as $file){
                        $path = public_path($file->link);
                        if(file_exists($path)){
                            unlink($path);
                        }
                        $file->delete();
                    }
                }

                // Delete User Family
                if($user->userFamily){
                    $user->userFamily->delete();
                }

                // Reset Admission Progress
                if($user->admissionProgress){
                    $user->admissionProgress->is_registration_complete = null;
                    $user->admissionProgress->is_passed_age = null;
                    $user->admissionProgress->save();
                }

                // Reset Note
                $student->note = null;
                $student->save();

                echo $id." Reverted <br>"; 
            }
        }
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        return $e->getMessage();
    }
});
