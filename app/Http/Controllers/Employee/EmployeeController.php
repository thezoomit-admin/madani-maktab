<?php

namespace App\Http\Controllers\Employee;

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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
 
    public function store(EmployeeStoreResource $request)
    {
        DB::beginTransaction();  
        try {  
            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }
 
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'password' => Hash::make($request->password),
                'user_type' => 'employee',  
                'profile_image' => $profilePicPath, 
                'role_id' => $request->role_id,
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
                'employee_id' => 111,
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
            
            DB::commit();  
            return api_response(null,'Employee has been created'); 

        } catch (\Exception $e) { 
            DB::rollBack();  
            return api_response(null, 'Error creating employee', $e->getMessage(), 500);
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
