<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\BaseController;
use App\Models\UserManager;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public $rolePermission;

    public function __construct(RolePermissionManagerInterface $rolePermission)
    {
        $this->rolePermission = $rolePermission;
    }
    /**
     * Login Process
     * 
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential'        => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error', $validator->errors());
        }

        $user = UserManager::where('email', $request->credential)->orWhere('phone_number', $request->credential)->first();
        // dd($user);
        if ($user != null) {
            $userBranch = $user->branch()->first();
            if (Hash::check($request->password, $user->password)) {
                if (!$user->hasVerifiedEmail()) {
                    return $request->wantsJson()
                        ? $this->sendError('Email Not Verified', "Please Verified Your Email")
                        : $this->sendResponse(array("success" => false), 'Email not verified');
                }
                $token = $user->createToken('admin_token')->plainTextToken;
                /* ALL DATA PERMISSIONS WILL ASSIGN HERE */
                $data = array();

                /* GET SCOPE PERMISSION FIRST */
                $scopes = $this->rolePermission->listAllPermissionScope();

                foreach ($scopes as $scope) {
                    $dataScope['id'] = $scope->id;
                    $dataScope['name'] = $scope->name;
                    $dataScope['order'] = $scope->order;
                    
                    $dataScope['permissions'] = array();
                    // check if current scope have permission, if not, then is_scope_access is false
                    if (count($scope->permissions) < 1) {
                        $dataScope['is_scope_access'] = false;
                    } 


                    /* GET PERMISSIONS FROM PERMISSION SCOPE */
                    foreach ($scope->permissions as $permission) {
                            $prm['id']              = $permission->id;
                            $prm['name']            = $permission->name;
                            $prm['display_name']    = $permission->display_name;
                            $prm['guard_name']      = $permission->guard_name;
                            $prm['order']           = $permission->order;

                            // check if current user doesn't have branch (it means this user is superadmin)
                            // the purpose for this checking is for permissions in company branch feature
                            // because the only user can access the company branch feature is superadmin or the user that directly assigned
                            // to branch prefix permissions. e.g (branch-list, branch-detail, etc) not from role
                            if ($userBranch == null) {
                                // check if current permission is have scope id with 7 value (it means this permissions is associated with branch)
                                // the purpose for this checking because the branch permissions is directly assigned to user not from role
                                if ($permission->scope_id == 7) {
                                    $prm['is_assigned']     = $user->hasPermissionTo($permission->name) ? true : false;
                                    array_push($dataScope['permissions'], $prm);
                                } else {
                                    $prm['is_assigned']     = $user->roles()->first()->hasPermissionTo($permission->name) ? true : false;
                                    array_push($dataScope['permissions'], $prm);
                                }
                            } else {
                                $prm['is_assigned']     = $userBranch->roles()->first()->hasPermissionTo($permission->name) ? true : false;
                                array_push($dataScope['permissions'], $prm);
                            }
                             // Count how many permission in current scope was assigned. If at least have one, then key 'is_scope_access' was true and if not then false
                            $dataScope['is_scope_access'] = arrFilterCount($dataScope['permissions'], 'is_assigned', true) > 0 ? true : false;
                    }

                    array_push($data, $dataScope);
                }

                if ($userBranch != null) {
                    $rolesName = ucfirst($userBranch->roles()->first()->name);
                    $branch = $userBranch;
                } else {
                    $rolesName = ucfirst($user->roles()->first()->name);
                    $branch = null;
                }
                return $this->sendResponse([
                    'token'         => $token,
                    'user_data'     => $user->makeHidden(['roles']),
                    'branch'        => $branch,
                    'expired_token' => Date::now()->addDay(150),
                    'role'          => $rolesName,
                    'permissions'   => $data
                ],'Login Successfully');
            }

            return $this->sendUnauthorized('Credential Not Found', null);
        } else {
            return $this->sendUnauthorized('Email or Phone Number Not Found',null);
        }
    }

    /**
     * Validate Token
     * 
     * @return Illuminate\Http\Response
    */
    public function validateToken()
    {
        try {
            if (auth('sanctum')->check()) {
                return $this->sendResponse(true, 'Validated');
            } else {
                return $this->sendUnauthorized(false, 'Validated');

            }
        } catch (\Exception $th) {
            return $this->sendError('Not Validated', $th->getMessage());
        }
        // return Auth::guard('api')->check();
    }

    /**
     * Logout Process
     * 
     * @return Illuminate\Http\Response
    */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Successfully Logout');
    }
}
