<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::select('id','name','slug')->get();
            return success_response($roles);
        } catch (\Exception $e) {
            return error_response($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name', 
            ]); 
            
            $role = Role::create([
                'name' => $request->name,
                'slug' => getSlug(new Role(),$request->name),
                'guard_name' => $request->guard_name ?? 'web',
            ]);

            DB::commit();
            return success_response(null, 'Role created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    } 

    public function update($id, Request $request)
    {
        DB::beginTransaction();
        try { 
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $id,  
            ]);
 
            $role = Role::findOrFail($id); 
            $role->update([
                'name' => $request->name,
                'slug' => getSlug(new Role(), $request->name),   
                'guard_name' => $request->guard_name ?? 'web',  
            ]);

            DB::commit();
            return success_response(null, 'Role updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    } 

    public function destroy($id)
    {
        DB::beginTransaction();
        try { 
            $role = Role::findOrFail($id); 
            $role->delete();  

            DB::commit();
            return success_response(null, 'Role deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return error_response($e->getMessage(), 500);
        }
    }





}
