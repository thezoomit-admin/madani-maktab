<?php

use App\Helpers\HijriDateService;
use App\Http\Controllers\Admin\Admission\AdmissionNoteController;
use App\Http\Controllers\Admin\Admission\FailToPassController;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Admin\Admission\InterviewStudentListController;
use App\Http\Controllers\Admin\Admission\PassToFailController;
use App\Http\Controllers\Admin\Admission\PreAdmissionTrialController;
use App\Http\Controllers\Admin\Admission\ProfilePrintStatusController;
use App\Http\Controllers\Admin\Admission\RegisterStudentListController;
use App\Http\Controllers\Admin\Admission\SendRegistrationNumberController;
use App\Http\Controllers\Admin\Admission\StudentController as AdmissionStudentController;
use App\Http\Controllers\Admin\Admission\TrialStudentListController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Employee\RoleController;
use App\Http\Controllers\Admin\Employee\RolePermissionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Common\CompanyCategoryApiController;
use App\Http\Controllers\Common\CountryApiController;
use App\Http\Controllers\Common\DepartmentController;
use App\Http\Controllers\Common\DesignationApiController;
use App\Http\Controllers\Common\DistrictApiController;
use App\Http\Controllers\Common\DivisionApiController;
use App\Http\Controllers\Common\FeeTypeController;
use App\Http\Controllers\Common\RoleApiController;
use App\Http\Controllers\Common\SessionController;
use App\Http\Controllers\Common\UnionApiController;
use App\Http\Controllers\Common\UpazilaApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Payment\BalanceController;
use App\Http\Controllers\Payment\OfficeTransactionController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\VendorPaymentController;
use App\Http\Controllers\Report\ActionLogReportController;
use App\Http\Controllers\Report\CollectionController;
use App\Http\Controllers\Report\DueReportController;
use App\Http\Controllers\Report\ExpenseController;
use App\Http\Controllers\Report\ExpenseReportController;
use App\Http\Controllers\Report\OjifaCollectReportController;
use App\Http\Controllers\Report\OjifaDetailsReportController;
use App\Http\Controllers\Report\TotalIncomeReportController;
use App\Http\Controllers\Setting\ExpenseCategoryController;
use App\Http\Controllers\Setting\ExpenseSubCategoryController;
use App\Http\Controllers\Setting\FeeSettingController; 
use App\Http\Controllers\Setting\HijriMonthController;
use App\Http\Controllers\Setting\MeasurmentUnitController;
use App\Http\Controllers\Student\StudentRegisterController;
use App\Http\Controllers\Setting\MeetLinkSettingController;
use App\Http\Controllers\Setting\PaymentMethodController;
use App\Http\Controllers\Setting\VendorController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\ExistingStudentController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ProfileUpdateController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\TeacherCommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('login', [AuthController::class, 'login'])->name('login'); 
Route::post('forget-password',[AuthController::class,"forgetPassword"]);
Route::post('register', [AuthController::class, 'register']);
Route::get('roles',RoleApiController::class);
Route::get('designations',DesignationApiController::class);
Route::get('company-categories',CompanyCategoryApiController::class);

// Location 
Route::get('countries',CountryApiController::class);
Route::get('divisions',DivisionApiController::class);
Route::get('districts',DistrictApiController::class);
Route::get('upazilas',UpazilaApiController::class);
Route::get('unions',UnionApiController::class);  
  

// Student Register 
Route::post('student-register-first-step',[StudentRegisterController::class,'firstStep']);
Route::post('student-register-last-step',[StudentRegisterController::class,'lastStep']);
Route::post('existing-student-register',[ExistingStudentController::class,'store']);
Route::get('student/{id}', [AdmissionStudentController::class,'student']);

//Student Registration
Route::get('student-register-last-stage', [AdmissionStudentController::class,'isCompleted']);
Route::get('dashboard',DashboardController::class);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('change-password',[AuthController::class,"changePassword"]); 
    
    Route::resource('role', RoleController::class); 
    Route::resource('role-permission',RolePermissionController::class);
    Route::resource('employee', EmployeeController::class);  
    Route::post('interview-schedule', [InterviewController::class,'schedule']);
    Route::post('interview-result',[InterviewController::class,'result']);

    Route::post('pre-trial-schedule', [PreAdmissionTrialController::class,'schedule']);
    Route::post('pre-trial-attend',[PreAdmissionTrialController::class,'attend']);
    Route::post('pre-trial-result',[PreAdmissionTrialController::class,'result']);

    Route::get('registerd-students', RegisterStudentListController::class); 
    Route::get('interview-students', InterviewStudentListController::class); 
    Route::get('trial-students', TrialStudentListController::class);
    Route::post('admission', [TrialStudentListController::class,'admission']);

    Route::delete('delete-registerd-students/{id}', [RegisterStudentListController::class,'delete']);

    Route::post('send-message', SendRegistrationNumberController::class);
    Route::get('fail_to_pass/{user_id}', FailToPassController::class);
    Route::get('pass_to_fail/{user_id}', PassToFailController::class); 
    Route::resource('admission-note',AdmissionNoteController::class);
    Route::put('update-print-status',[ProfilePrintStatusController::class,'updateStatus']); 

    // setting
    Route::get('meet-link',[MeetLinkSettingController::class,'index']);
    Route::post('meet-link',[MeetLinkSettingController::class,'update']);
    Route::resource('fee-setting',FeeSettingController::class);
    Route::resource('expense-category',ExpenseCategoryController::class);
    Route::resource('expense-sub-category',ExpenseSubCategoryController::class);
    Route::resource('measurment-unit',MeasurmentUnitController::class);
    Route::resource('vendor',VendorController::class);

    // Hijri Date  
    Route::resource('hijri-month',HijriMonthController::class); 
    Route::get('active-unactive-month/{id}',[HijriMonthController::class,"changeStatus"]); 

    // Student Management System 
    Route::get('existing-student-list',[ExistingStudentController::class,"index"]);
    Route::post('existing-student-approve/{id}',[ExistingStudentController::class,"approve"]); 

    Route::resource('teacher-comment',TeacherCommentController::class); 
    Route::get('student',[StudentController::class,'index']);
    Route::post('update-roll/{id}',[StudentController::class,'updateRoll']);
    Route::delete('student/delete/{id}',[StudentController::class,'delete']);
    Route::get('profile/{id?}', [ProfileController::class, 'profile']);
    Route::get('payment-history/{id?}', [ProfileController::class, 'PaymentHistory']);
    Route::get('enroll-history/{id?}', [ProfileController::class, 'EnroleHistory']);
    Route::get('change-fee-type/{id}', [ProfileController::class, 'ChangeFeeType']);

    // update profile  
    Route::prefix('profile/update/{id}')->group(function () {
        Route::post('/basic',       [ProfileUpdateController::class, 'updateBasic']);
        Route::post('/education',   [ProfileUpdateController::class, 'updateEducation']);
        Route::post('/address',     [ProfileUpdateController::class, 'updateAddress']);
        Route::post('/guardian',    [ProfileUpdateController::class, 'updateGuardian']);
        Route::post('/family',      [ProfileUpdateController::class, 'updateFamily']);

        // Answer-files
        Route::post   ('/answer-file',        [ProfileUpdateController::class, 'storeAnswerFile']);
        Route::delete ('/answer-file', [ProfileUpdateController::class, 'destroyAnswerFile']);
    });

    // Payment Route  
    Route::resource('payment-method',PaymentMethodController::class);
    Route::post('pay-now',[PaymentController::class,'payNow']);
    Route::get('payment-list',[PaymentController::class,'paymentList']);
    Route::get('approve-payment/{id}',[PaymentController::class,'approvePayment']);   

    // Report 
    Route::get('ojifa-report',[OjifaDetailsReportController::class,'OjifaReport']);
    Route::get('ojifa-collect-report',[OjifaCollectReportController::class,'getStudentPaymentReport']);
    Route::get('due-report',[DueReportController::class,'index']);
    Route::get('due-payment-report',[DueReportController::class,'paymentList']);
    Route::post('due-pay',[VendorPaymentController::class,'payment']);
    Route::get('total-report',[TotalIncomeReportController::class,'index']);
    Route::get('monthwise-expense',[ExpenseReportController::class,'getArabicMonthWiseExpenseReport']);

    // Income Report 
    Route::get('income-balance',[BalanceController::class,'incomeBalance']);
    Route::post('deposit',[OfficeTransactionController::class,'deposit']);
    Route::get('deposit-list',[OfficeTransactionController::class,'depositList']);

    // Expense Reprot  
    Route::get('expense-balance',[BalanceController::class,'expenseBalance']); 
    Route::resource('expense',ExpenseController::class);
    Route::post('collection',[CollectionController::class,'collection']);
    Route::get('collection-list',[CollectionController::class,'collectionList']); 


    // Attendance  
    Route::get('attendance/{reg_id?}',[AttendanceController::class,'attendance']);
    Route::post('out-reason',[AttendanceController::class,'outReason']);

    Route::get('/action-log',[ActionLogReportController::class,'index']);
});  

// Common 
Route::get('month-list',[HijriMonthController::class,'month_list']);
Route::get('year-list',[HijriMonthController::class,'year_list']);
Route::get('maktab-session',[SessionController::class,'maktabSession']);
Route::get('kitab-session',[SessionController::class,'kitabSession']);
Route::get('fee-type',[FeeTypeController::class,'feeList']); 
Route::get('departments',[DepartmentController::class,'index']);

Route::get('test-date',function(Request $request){
    $hijriService = new HijriDateService();
    return $hijriService->getHijri($request->date);
});

 

 
