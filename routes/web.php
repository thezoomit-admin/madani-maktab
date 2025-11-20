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
use PhpParser\Node\Expr\FuncCall;

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

Route::get('/remove-url',function(){
     $baseUrl = 'https://madani.zoomdigital.net/';
     $count = 0;
     
     // 1. Users - profile_image
     $users = User::whereNotNull('profile_image')->get();
     foreach($users as $user){
          if(strpos($user->profile_image, $baseUrl) !== false){
               $user->profile_image = str_replace($baseUrl, '', $user->profile_image);
               $user->save();
               $count++;
          }
     }
     
     // 2. Student Registers - handwriting_image
     $registers = StudentRegister::whereNotNull('handwriting_image')->get();
     foreach($registers as $register){
          if(strpos($register->handwriting_image, $baseUrl) !== false){
               $register->handwriting_image = str_replace($baseUrl, '', $register->handwriting_image);
               $register->save();
               $count++;
          }
     }
     
     // 3. Expenses - image
     $expenses = Expense::whereNotNull('image')->get();
     foreach($expenses as $expense){
          if(strpos($expense->image, $baseUrl) !== false){
               $expense->image = str_replace($baseUrl, '', $expense->image);
               $expense->save();
               $count++;
          }
     }
     
     // 4. Vendor Payments - image
     $vendorPayments = VendorPayment::whereNotNull('image')->get();
     foreach($vendorPayments as $payment){
          if(strpos($payment->image, $baseUrl) !== false){
               $payment->image = str_replace($baseUrl, '', $payment->image);
               $payment->save();
               $count++;
          }
     }
     
     // 5. Payment Transactions - image
     $paymentTransactions = PaymentTransaction::whereNotNull('image')->get();
     foreach($paymentTransactions as $transaction){
          if(strpos($transaction->image, $baseUrl) !== false){
               $transaction->image = str_replace($baseUrl, '', $transaction->image);
               $transaction->save();
               $count++;
          }
     }
     
     // 6. Office Transactions - image
     $officeTransactions = OfficeTransaction::whereNotNull('image')->get();
     foreach($officeTransactions as $transaction){
          if(strpos($transaction->image, $baseUrl) !== false){
               $transaction->image = str_replace($baseUrl, '', $transaction->image);
               $transaction->save();
               $count++;
          }
     }
     
     // 7. Payment Methods - icon
     $paymentMethods = PaymentMethod::whereNotNull('icon')->get();
     foreach($paymentMethods as $method){
          if(strpos($method->icon, $baseUrl) !== false){
               $method->icon = str_replace($baseUrl, '', $method->icon);
               $method->save();
               $count++;
          }
     }
     
     // 8. Product Images - image_path
     $productImages = ProductImage::whereNotNull('image_path')->get();
     foreach($productImages as $productImage){
          if(strpos($productImage->image_path, $baseUrl) !== false){
               $productImage->image_path = str_replace($baseUrl, '', $productImage->image_path);
               $productImage->save();
               $count++;
          }
     }
     
     // 9. Answer Files - link
     $answerFiles = AnswerFile::whereNotNull('link')->get();
     foreach($answerFiles as $file){
          if(strpos($file->link, $baseUrl) !== false){
               $file->link = str_replace($baseUrl, '', $file->link);
               $file->save();
               $count++;
          }
     }
     
     return "URL removed successfully! Total records updated: {$count}";
});

Route::get('/refresh', function () {  
   
     DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 
     // Admission::where('status', 1)->update(['status' => 0]);
     // User::whereNotNull('reg_id')->update(['reg_id' => null]);
  
     // Student::truncate();
     // Enrole::truncate();
     // TeacherComment::truncate();
     // Payment::truncate();
     // PaymentTransaction::truncate(); 
     $payments_methods = PaymentMethod::all();
     foreach($payments_methods as $method){
          // $method->income_in_hand = 0;
          $method->expense_in_hand = 0;
          $method->balance = $method->income_in_hand; 
          $method->save();
     }
     // OfficeTransaction::truncate();
     Expense::truncate();
     DB::statement('SET FOREIGN_KEY_CHECKS=1;');
     return 'Refresh completed successfully!';
});
 
