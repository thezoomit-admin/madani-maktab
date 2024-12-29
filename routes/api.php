<?php 
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Admin\Admission\InterviewStudentListController;
use App\Http\Controllers\Admin\Admission\PreAdmissionTrialController;
use App\Http\Controllers\Admin\Admission\RegisterStudentListController;
use App\Http\Controllers\Admin\Admission\StudentController;
use App\Http\Controllers\Admin\Admission\TrialStudentListController;
use App\Http\Controllers\AdminEmployee\EmployeeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Common\CompanyCategoryApiController;
use App\Http\Controllers\Common\CountryApiController;
use App\Http\Controllers\Common\DesignationApiController;
use App\Http\Controllers\Common\DistrictApiController;
use App\Http\Controllers\Common\DivisionApiController;
use App\Http\Controllers\Common\RoleApiController;
use App\Http\Controllers\Common\UnionApiController;
use App\Http\Controllers\Common\UpazilaApiController; 
use App\Http\Controllers\Student\StudentRegisterController;
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
Route::get('student/{id}', [StudentController::class,'student']); 
 
Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('employee', EmployeeController::class);  
    Route::resource('interview-schedule', InterviewController::class);
    Route::post('interview-result',[InterviewController::class,'result']);
    Route::resource('pre-admission-trial', PreAdmissionTrialController::class); 
    Route::post('pre-trial-attend',[PreAdmissionTrialController::class,'attend']);
    Route::post('pre-trial-result',[PreAdmissionTrialController::class,'result']);

    Route::get('registerd-students', RegisterStudentListController::class); 
    Route::get('interview-students', InterviewStudentListController::class); 
    Route::get('trial-students', TrialStudentListController::class);
    Route::get('student-register-last-stage', [StudentController::class,'isCompleted']);
});


 
