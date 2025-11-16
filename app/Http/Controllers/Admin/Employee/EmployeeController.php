<?php

namespace App\Http\Controllers\Admin\Employee;
 
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreResource; 
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{ 
    public function index()
    {
        try {
            $data = User::where('user_type', 'teacher')
                        ->where('deleted_at', null) 
                        ->get();
            
            $result = $data->map(function ($user) {
                $currentRole = get_current_role($user->id);
                return [
                    'id' => $user->id,
                    'reg_id' => $user->reg_id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'profile_image' => $user->profile_image,
                    'role' => $currentRole ? $currentRole['role_name'] : null,
                    'role_details' => $currentRole,
                ];
            });

            return success_response($result);  
        } catch (\Exception $e) {   
            return error_response($e->getMessage(), 500);  
        }
    }

 
    public function store(EmployeeStoreResource $request)
    {
        DB::beginTransaction();  
        try { 
            if (User::where('email', $request->user_email)->exists()) {
                return error_response(null,400,"Email already exists!");
            }
     
            $profilePicPath = null;
            if ($request->hasFile('profile_image')) {
                $profilePicPath = $request->file('profile_image')->store('profile_images', 'public');
            }
     
            $user = User::create([
                'reg_id' => $request->reg_id,
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'password' => Hash::make("12345678"),  
                'user_type' => 'teacher',  
                'profile_image' => $profilePicPath, 
            ]);   
            // Create initial employee role
            \App\Models\EmployeeRole::create([
                'user_id' => $user->id,
                'role_id' => $request->role_id,
                'start_date' => now(),
            ]);
    
            DB::commit();   
    
            return success_response(null, 'Employee has been created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  
            return error_response($e->getMessage(), 500);   
        }
    }


    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,email,' . $id,
            'user_phone' => 'nullable|string|max:20',
            'reg_id' => 'nullable|string|max:255|unique:users,reg_id,' . $id,
            'profile_image' => 'nullable',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first()); // প্রথম error message return করবে
        }

        DB::beginTransaction();  
        try {
            $user = User::findOrFail($id);   
             if ($request->hasFile('profile_image')) { 
                $profileImage = $request->file('profile_image'); 
                $profileImageName = time() . '_' . $profileImage->getClientOriginalName(); 
                $profileImage->move(public_path('uploads/profile_images'), $profileImageName); 
                $profileImageUrl = asset('uploads/profile_images/' . $profileImageName);

                $user->profile_image = $profileImageUrl;
                $user->save();
            }  
            $user->update([
                'reg_id' => $request->reg_id,
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,  
            ]); 
            DB::commit();   
            return success_response(null, 'Employee details have been updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); 
            return error_response($e->getMessage());  
        }
    }

    public function changeRole($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'start_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return error_response($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Close previous active role (if any)
            $current = \App\Models\EmployeeRole::where('user_id', $user->id)
                ->whereNull('end_date')
                ->orderByDesc('start_date')
                ->first();
            if ($current) {
                $current->end_date = $request->start_date;
                $current->save();
            }

            // Assign new role
            \App\Models\EmployeeRole::create([
                'user_id' => $user->id,
                'role_id' => $request->role_id,
                'start_date' => $request->start_date,
            ]);

            DB::commit();
            return success_response(null, 'Employee role changed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "Not found");
            }
            $user->delete();
            DB::commit();
            return success_response(null, 'Employee has been deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage());
        }
    }



    
     
}
