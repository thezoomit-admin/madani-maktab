<?php
namespace App\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AffiliatePayoutInfo;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class LoginService {

    public static function createResponse($user)
    {
        $token = $user->createToken('authToken')->plainTextToken; 
        if($user->user_type=="student"){
            $permission = get_permissions($user->id, 'student');
            $role = 'student';
        }else{
            $permission = get_permissions($user->id);
            $currentRole = get_current_role($user->id);
            $role = null;
            if ($currentRole && isset($currentRole['role_id'])) {
                $roleModel = Role::find($currentRole['role_id']);
                $role = $roleModel ? $roleModel->slug : null;
            }
        } 
        
        $data = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'role' =>  $role,
            ],
            'permissions' => $permission,
        ];   
        return success_response($data, 'User authenticated successfully.'); 
    }

}