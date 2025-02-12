<?php

namespace App\Http\Controllers\Admin\Employee;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Exception;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function show($role_id){
        $role = Role::find($role_id); 
        $role_permission = $role->permissions; 
        $all_permission = Permission::get(); 
        $permission = []; 
        foreach ($all_permission as $perm) {
            $permission[] = [
                'id' => $perm->id,
                'name' => $perm->name,
                'is_checked' => $role_permission->contains(function($rolePerm) use ($perm) {
                    return $rolePerm->pivot->permission_id == $perm->id;
                })
            ];
        } 
        return $permission; 
    }  

    public function update(Request $request, $role_id) {
        try { 
            RolePermission::where('role_id', $role_id)->delete(); 
            $permissions = $request->permissions; 
            if (isset($permissions) && count($permissions) > 0) { 
                foreach ($permissions as $permission) {
                    RolePermission::create([
                        'role_id' => $role_id,
                        'permission_id'  => $permission,
                    ]);
                }
            } 
            return success_response(null, "Permission updated");
        } catch (Exception $e) { 
            return error_response($e->getMessage());
        }
    }
}
