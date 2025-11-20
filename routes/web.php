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

Route::get('/remove-url', function () {
     $yearPrefix = '1446';
     $updated = 0;

     $normalizePath = function (?string $path) use ($yearPrefix) {
          if (blank($path)) {
               return $path;
          }

          $clean = ltrim($path, '/');

          if ($clean === '') {
               return null;
          }

          if (!Str::startsWith($clean, 'uploads/')) {
               $clean = 'uploads/' . ltrim($clean, '/');
          }

          $relative = ltrim(Str::after($clean, 'uploads/'), '/');

          if (!Str::startsWith($relative, $yearPrefix . '/')) {
               $relative = ltrim($relative, '/');
               $relative = $relative ? ($yearPrefix . '/' . $relative) : $yearPrefix;
          }

          $relative = preg_replace('#/+#', '/', $relative);

          return 'uploads/' . $relative;
     };

     $processColumn = function ($builder, string $column) use (&$updated, $normalizePath) {
          $builder->whereNotNull($column)->chunkById(500, function ($models) use ($column, &$updated, $normalizePath) {
               foreach ($models as $model) {
                    $newPath = $normalizePath($model->$column);
                    if ($newPath !== $model->$column) {
                         $model->$column = $newPath;
                         $model->save();
                         $updated++;
                    }
               }
          });
     };

     $processColumn(User::query(), 'profile_image');
     $processColumn(StudentRegister::query(), 'handwriting_image');
     $processColumn(Expense::query(), 'image');
     $processColumn(VendorPayment::query(), 'image');
     $processColumn(PaymentTransaction::query(), 'image');
     $processColumn(OfficeTransaction::query(), 'image');
     $processColumn(PaymentMethod::query(), 'icon');
     $processColumn(ProductImage::query(), 'image_path');
     $processColumn(AnswerFile::query(), 'link');

     return "Normalized {$updated} records.";
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
 
