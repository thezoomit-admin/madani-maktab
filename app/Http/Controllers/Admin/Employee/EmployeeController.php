<?php

namespace App\Http\Controllers\Admin\Employee;
 
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStoreResource; 
use App\Models\User;
use App\Traits\HandlesImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use HandlesImageUpload; 
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
     
            $profilePicPath = $this->uploadImage($request, 'profile_image', 'uploads/profile_images');
     
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
                $profileImageUrl = $this->uploadImage($request, 'profile_image', 'uploads/profile_images');
                if ($profileImageUrl) {
                    $user->profile_image = $profileImageUrl;
                    $user->save();
                }
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

    public function show($id){
        
        try {
            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "Not found");
            }

            // Contacts
            $contact = \App\Models\UserContact::where('user_id', $user->id)->first();

            // Employee meta
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();

            // Current role
            $currentRole = get_current_role($user->id);

            // Guardian (father)
            $guardianFather = \App\Models\Guardian::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('guardian_relation', 'father')
                      ->orWhere('guardian_relation', 'Father')
                      ->orWhere('guardian_relation', 'বাবা'); // possible localized value
                })
                ->first();

            // Addresses
            $presentAddress = \App\Models\UserAddress::where('user_id', $user->id)
                ->where('address_type', 'temporary')
                ->first();

            $permanentAddress = \App\Models\UserAddress::where('user_id', $user->id)
                ->where('address_type', 'permanent')
                ->first();

            // Field-wise address payloads
            $sameAddress = null;
            if ($guardianFather && $guardianFather->same_address !== null) {
                $sameAddress = (bool)$guardianFather->same_address;
            } else {
                // Fallback: if no temporary address exists, consider same_address true
                $sameAddress = $presentAddress ? true : false;
            }

            $presentPayload = $presentAddress ? [
                'house_or_state' => $presentAddress->house_or_state,
                'village_or_area' => $presentAddress->village_or_area,
                'post_office' => $presentAddress->post_office,
                'upazila_thana' => $presentAddress->upazila_thana,
                'district' => $presentAddress->district,
                'division' => $presentAddress->division,
                'same_address' => $sameAddress,
            ] : [
                'house_or_state' => null,
                'village_or_area' => null,
                'post_office' => null,
                'upazila_thana' => null,
                'district' => null,
                'division' => null,
                'same_address' => $sameAddress,
            ];

            $permanentPayload = $permanentAddress ? [
                'house_or_state' => $permanentAddress->house_or_state,
                'village_or_area' => $permanentAddress->village_or_area,
                'post_office' => $permanentAddress->post_office,
                'upazila_thana' => $permanentAddress->upazila_thana,
                'district' => $permanentAddress->district,
                'division' => $permanentAddress->division,
            ] : null;

            $result = [
                'basic' => [
                    'name' => $user->name,
                    'profile_image' => $user->profile_image,
                    'office_phone' => $contact->office_phone ?? null,
                    'personal_phone' => $contact->personal_phone ?? null,
                    'email' => $user->email,
                    'reg_id' => $user->reg_id,
                    'whatsapp' => $contact->imo_number ?? null,
                    'dob' => $user->dob,
                    'blood_group' => $user->blood_group,
                    'status' => optional($employee)->status,
                    'description' => optional($employee)->description,
                    'education_qualification' => optional($employee)->education_qualification,
                    'previous_work_details' => optional($employee)->previous_work_details,
                    'maritial_status' => optional($employee)->maritial_status,
                    'role_name' => $currentRole ? $currentRole['role_name'] : null,
                ],
                'family' => [
                    'fathers_name' => $guardianFather->guardian_name ?? null,
                    'children_count' => $guardianFather->children_count ?? null,
                    'child_education' => $guardianFather->child_education ?? null,
                    'maritial_status' => optional($employee)->maritial_status,
                ],
                'address' => [
                    'present' => $presentPayload,
                    'permanent' => $permanentPayload,
                ],
            ];

            return success_response($result);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

    public function updateBasic($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:255',
            'user_email' => 'nullable|email|unique:users,email,' . $id,
            'user_phone' => 'nullable|string|max:20',
            'reg_id' => 'nullable|string|max:255|unique:users,reg_id,' . $id,
            'office_phone' => 'nullable|string|max:20',
            'personal_phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'status' => 'nullable|in:0,1',
            'description' => 'nullable|string',
            'education_qualification' => 'nullable|string',
            'previous_work_details' => 'nullable|string',
            'maritial_status' => 'nullable|string|max:50',
            'profile_image' => 'nullable|image|max:2048',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            $user->update([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'phone' => $request->user_phone,
                'reg_id' => $request->reg_id,
                'dob' => $request->dob,
                'blood_group' => $request->blood_group,
            ]);

            if ($request->hasFile('profile_image')) {
                $profileImageUrl = $this->uploadImage($request, 'profile_image', 'uploads/profile_images');
                if ($profileImageUrl) {
                    $user->profile_image = $profileImageUrl;
                    $user->save();
                }
            }

            $employee = \App\Models\Employee::firstOrCreate(['user_id' => $user->id]);
            $employee->description = $request->description;
            $employee->education_qualification = $request->education_qualification;
            $employee->previous_work_details = $request->previous_work_details;
            if (!is_null($request->maritial_status)) {
                $employee->maritial_status = $request->maritial_status;
            }
            if (!is_null($request->status)) {
                $employee->status = (int)$request->status;
            }
            $employee->save();

            $contact = \App\Models\UserContact::firstOrCreate(['user_id' => $user->id]);
            $contact->office_phone = $request->office_phone;
            $contact->personal_phone = $request->personal_phone;
            if ($request->filled('whatsapp')) {
                $contact->imo_number = $request->whatsapp;
            }
            $contact->save();

            DB::commit();
            return success_response(null, 'Basic info updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    }

    public function updateFamily($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fathers_name' => 'nullable|string|max:255',
            'children_count' => 'nullable|integer|min:0',
            'child_education' => 'nullable',
            'maritial_status' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Update employee marital status if provided
            if ($request->filled('maritial_status')) {
                $employee = \App\Models\Employee::firstOrCreate(['user_id' => $user->id]);
                $employee->maritial_status = $request->maritial_status;
                $employee->save();
            }

            // Upsert Guardian as father
            $guardian = \App\Models\Guardian::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('guardian_relation', 'father')
                      ->orWhere('guardian_relation', 'Father')
                      ->orWhere('guardian_relation', 'বাবা');
                })->first();
            if (!$guardian) {
                $guardian = new \App\Models\Guardian();
                $guardian->user_id = $user->id;
                $guardian->guardian_relation = 'father';
            }
            if ($request->filled('fathers_name')) {
                $guardian->guardian_name = $request->fathers_name;
            }
            if (!is_null($request->children_count)) {
                $guardian->children_count = (int)$request->children_count;
            }
            if (!is_null($request->child_education)) {
                $guardian->child_education = $request->child_education;
            }
            $guardian->guardian_occupation_details = "default";
            $guardian->guardian_education = "default";

            $guardian->save();

            DB::commit();
            return success_response(null, 'Family info updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    }

    public function updateAddress($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'house_or_state'      => 'required|string|max:255',
            'village_or_area'     => 'required|string|max:255',
            'post_office'         => 'required|string|max:255',
            'upazila_thana'       => 'required|string|max:255',
            'district'            => 'required|string|max:255',
            'division'            => 'required|string|max:255',

            'same_address'                 => 'nullable|boolean',
            'temporary_house_or_state'     => 'required_if:same_address,false|string|max:255',
            'temporary_village_or_area'    => 'required_if:same_address,false|string|max:255',
            'temporary_post_office'        => 'required_if:same_address,false|string|max:255',
            'temporary_upazila_thana'      => 'required_if:same_address,false|string|max:255',
            'temporary_district'           => 'required_if:same_address,false|string|max:255',
            'temporary_division'           => 'required_if:same_address,false|string|max:255',
        ]);
        if ($validator->fails()) {
            return error_response($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Permanent address
            \App\Models\UserAddress::updateOrCreate(
                ['user_id' => $user->id, 'address_type' => 'permanent'],
                [
                    'house_or_state'    => $request->house_or_state,
                    'village_or_area'   => $request->village_or_area,
                    'post_office'       => $request->post_office,
                    'upazila_thana'     => $request->upazila_thana,
                    'district'          => $request->district,
                    'division'          => $request->division,
                ]
            );

            // Temporary address or mirror based on same_address
            if (!$request->same_address) {
                \App\Models\UserAddress::updateOrCreate(
                    ['user_id' => $user->id, 'address_type' => 'temporary'],
                    [
                        'house_or_state'    => $request->temporary_house_or_state,
                        'village_or_area'   => $request->temporary_village_or_area,
                        'post_office'       => $request->temporary_post_office,
                        'upazila_thana'     => $request->temporary_upazila_thana,
                        'district'          => $request->temporary_district,
                        'division'          => $request->temporary_division,
                    ]
                );
            } else {
                \App\Models\UserAddress::where('user_id', $user->id)
                    ->where('address_type', 'temporary')
                    ->delete();
            }

            DB::commit();
            return success_response(null, 'Address info updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
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
    
     
    public function roleHistory($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return error_response(null, 404, "Not found");
            }

            $roles = \App\Models\EmployeeRole::where('user_id', $user->id)
                ->orderBy('start_date', 'asc')
                ->get()
                ->map(function ($er) {
                    $role = \App\Models\Role::find($er->role_id);
                    return [
                        'role_id' => $er->role_id,
                        'role_name' => $role?->name,
                        'start_date' => $er->start_date,
                        'end_date' => $er->end_date,
                        'is_current' => $er->end_date === null,
                    ];
                });

            return success_response($roles);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }
}
