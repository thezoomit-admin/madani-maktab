<?php

namespace App\Http\Controllers\Employee;

use App\Helpers\ReportingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreResource;
use App\Models\Employee;
use App\Models\EmployeeDesignation;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserContact;
use App\Models\UserReporting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{ 
    public function index()
    {
        try {
            $data = DB::table('users')
                ->leftJoin('employees', 'users.id', '=', 'employees.user_id')  
                ->leftJoin('employee_designations', function ($join) {
                    $join->on('employees.id', '=', 'employee_designations.employee_id')
                        ->whereNull('employee_designations.end_date');  
                })  
                ->leftJoin('designations', 'employee_designations.designation_id', '=', 'designations.id')  
                ->select( 
                    'employees.id as id',
                    'users.id as user_id', 
                    'employees.employee_id', 
                    'users.profile_image', 
                    'users.name',  
                    'users.phone', 
                    'users.email', 
                    'users.senior_user', 
                    'users.junior_user',
                    'designations.title as designation'
                )
                ->where('user_type','employee')
                ->where('users.user_type', 'employee') // Filter only employee users
                ->get();

            return api_response($data);


        } catch (\Exception $e) {   
            return api_response(null, 'An error occurred while fetching designations', false, 500, ['exception' => $e->getMessage()]);
        }
    }
 
    public function store(EmployeeStoreResource $request)
    {
        DB::beginTransaction();  
        try {  
            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }
 
            $auth_user = User::find(auth()->id);
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'password' => Hash::make("12345678"),
                'user_type' => 'employee',  
                'profile_image' => $profilePicPath, 
                'role_id' => $request->role_id,
                'company_id' => $auth_user->company_id, 
            ]); 
 
            UserContact::create([
                'user_id' => $user->id,
                'name' => $request->name ?? $request->user_name,
                'office_phone' => $request->office_phone,
                'personal_phone' => $request->personal_phone,
                'office_email' => $request->office_email,
                'personal_email' => $request->personal_email,
                'emergency_contact_number' => $request->emergency_contact_number,
                'emergency_contact_person' => $request->emergency_contact_person,
            ]);
 
            UserAddress::create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'division_id' => $request->division_id,
                'district_id' => $request->district_id,
                'upazila_id' => $request->upazila_id,
                'address' => $request->address,
            ]);
 
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => Employee::generateNextEmployeeId(),
                'status' => 1,
            ]); 

            // Create Employee Designation
            EmployeeDesignation::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'designation_id' => $request->designation_id,
                'start_date' => now() 
            ]); 

            UserReporting::create([
                'user_id' => $user->id, 
                'reporting_user_id' => $request->reporting_user_id,
                'start_date' => now() 
            ]);

            $user->senior_user = ReportingService::getAllSenior($user->id);
            $user->junior_user = ReportingService::getAllJunior($user->id);
            $user->save();
            
            DB::commit();  
            return api_response(null,'Employee has been created'); 

        } catch (\Exception $e) { 
            DB::rollBack();  
            return api_response(null, 'An error occurred while fetching designations', false, 500, $e->getMessage());
        }
    }

    public function show(string $id)
    {
        //
    }

    
    public function update(Request $request, string $id)
    {
        //
    }

    
    public function destroy(string $id)
    {
        //
    }
}
