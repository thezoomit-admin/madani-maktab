<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Common\CompanyCategoryApiController;
use App\Http\Controllers\Common\CountryApiController;
use App\Http\Controllers\Common\DesignationApiController;
use App\Http\Controllers\Common\DistrictApiController;
use App\Http\Controllers\Common\DivisionApiController;
use App\Http\Controllers\Common\RoleApiController;
use App\Http\Controllers\Common\UnionApiController;
use App\Http\Controllers\Common\UpazilaApiController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductController;
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

Route::resource('employee', EmployeeController::class); 
Route::resource('product-category', ProductCategoryController::class); 
Route::resource('product', ProductController::class); 

// Student Register 
Route::post('student-register-first-step',[StudentRegisterController::class,'firstStep']);
Route::post('student-register-last-step',[StudentRegisterController::class,'lastStep']);

Route::middleware(['auth:api'])->group(function () {
    // Route::resource('employee', EmployeeController::class); 
});

 
