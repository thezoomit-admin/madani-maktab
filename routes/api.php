<?php

use App\Http\Controllers\Admin\Admission\AdmissionNoteController;
use App\Http\Controllers\Admin\Admission\FailToPassController;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Admin\Admission\InterviewStudentListController;
use App\Http\Controllers\Admin\Admission\PassToFailController;
use App\Http\Controllers\Admin\Admission\PreAdmissionTrialController;
use App\Http\Controllers\Admin\Admission\ProfilePrintStatusController;
use App\Http\Controllers\Admin\Admission\RegisterStudentListController;
use App\Http\Controllers\Admin\Admission\SendRegistrationNumberController;
use App\Http\Controllers\Admin\Admission\StudentController;
use App\Http\Controllers\Admin\Admission\TrialStudentListController;
use App\Http\Controllers\Admin\Employee\EmployeeController;
use App\Http\Controllers\Admin\Employee\RoleController;
use App\Http\Controllers\Admin\Employee\RolePermissionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Common\CompanyCategoryApiController;
use App\Http\Controllers\Common\CountryApiController;
use App\Http\Controllers\Common\DesignationApiController;
use App\Http\Controllers\Common\DistrictApiController;
use App\Http\Controllers\Common\DivisionApiController;
use App\Http\Controllers\Common\RoleApiController;
use App\Http\Controllers\Common\UnionApiController;
use App\Http\Controllers\Common\UpazilaApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Setting\FeeSettingController;
use App\Http\Controllers\Setting\HijriDateController;
use App\Http\Controllers\Setting\HijriYearController;
use App\Http\Controllers\Student\StudentRegisterController;
use App\Http\Controllers\Setting\MeetLinkSettingController;
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
Route::post('existing-student-register',[StudentRegisterController::class,'existing']);
Route::get('student/{id}', [StudentController::class,'student']);

//Student Registration
Route::get('student-register-last-stage', [StudentController::class,'isCompleted']);
Route::get('dashboard',DashboardController::class);
Route::middleware(['auth:sanctum'])->group(function () {
    
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

    // Hijri Date 
    Route::resource('hijri-year',HijriYearController::class);
    Route::get('hijri-month',[HijriDateController::class,'month']);
    Route::resource('hijri-date',HijriDateController::class);



    // Student Management System 
    Route::get('existing-student-list',[StudentRegisterController::class,"existingStudent"]);
    
});


 
