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
Route::get('test-sms',function(){
        $message = "This is a test message from Maktab System.";
        $phone = "+8801796351081";
        $messageService = new PhoneMessageService;
        $response = $messageService->sendMessage($phone, $message);  
        dd($response);
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
    Payment::truncate(); 
    PaymentTransaction::truncate();  
    $payment_methods = PaymentMethod::all();
    foreach ($payment_methods as $method) {
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

 
Route::get('/revert-last-step', function() {
    $reg_ids = ['ম-036', 'ম-051', 'ম-059', 'ম-090', 'ম-092', 'ম-100', 'ম-138', 'ম-141', 'ম-154', 'ম-204', 'ম-285', 'ম-366', 'ম-407','ম-157', 'ম-068', 'ম-084', 'ম-091', 'ম-119', 'ম-144', 'ম-157', 'ম-181', 'ম-199', 'ম-221', 'ম-341.342', 'ম-353', 'ম-361'];
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

Route::get('/fix-enrollments-1447', function () {
    DB::beginTransaction();
    try {
        $year = 1447;
        
        // Find enrollments for the specified year
        $enrolesToDelete = Enrole::where('year', $year)->get();
        $count = $enrolesToDelete->count();
        
        if ($count === 0) {
            return "No enrollments found for year $year.";
        }

        foreach ($enrolesToDelete as $enrole) {
            $studentId = $enrole->student_id;
            
            // 1. Delete associated payments and transactions
            $payments = Payment::where('enrole_id', $enrole->id)->get();
            foreach ($payments as $payment) {
                // Delete payment transactions linked to this payment
                PaymentTransaction::where('payment_id', $payment->id)->delete();
                $payment->delete();
            }

            // 2. Delete the enrollment itself
            $enrole->delete();

            // 3. Find the previous enrollment and set it to active (status 1)
            $previousEnrole = Enrole::where('student_id', $studentId)
                ->orderByDesc('id')
                ->first(); // Since we just deleted the latest, this should be the previous one

            if ($previousEnrole) {
                $previousEnrole->status = 1; // Set to active/running
                $previousEnrole->save();
            }
        }

        DB::commit();
        return "Successfully removed $count enrollments for year $year and reverted to previous active status.";

    } catch (\Exception $e) {
        DB::rollBack();
        return "Error: " . $e->getMessage();
    }
});
