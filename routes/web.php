<?php

use App\Enums\FeeType;
use App\Helpers\ReportingService;
use App\Http\Controllers\Admin\Admission\InterviewController;
use App\Http\Controllers\Student\AttendanceSyncController;
use App\Models\Admission;
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

Route::get('/',function(Request $request){
     $year = $request->input('year'); 

        if (!$year) {
            $activeHijriDate = HijriMonth::where('is_active', true)->first();
            if (!$activeHijriDate) {
                return error_response(null, 404, 'Active Hijri year not found.');
            }
            $year = $activeHijriDate->year;
        }

        $dates = HijriMonth::where('year', $year)
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        if (!$dates || !$dates->start_date || !$dates->end_date) {
            return error_response(null, 404, 'No Hijri months found for the specified year.');
        }

        $startDate = $dates->start_date;
        $endDate = $dates->end_date;

        $vendors = Vendor::select('name','id')
            ->withCount([
                'expenses as total_expense' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->select(DB::raw("COALESCE(SUM(total_amount), 0)"));
                },
                'payments as total_payment' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->select(DB::raw("COALESCE(SUM(amount), 0)"));
                }
            ])
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    // 'total_expense' => $vendor->total_expense,
                    // 'total_payment' => $vendor->total_payment,
                    'total_due' => $vendor->due,
                ];
            });

        // Calculate total due across all vendors
        $totalDue = $vendors->sum('total_due');

        return success_response([ 
            'total_due' => $totalDue,
            'vendors' => $vendors,
        ]);
});


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
 
