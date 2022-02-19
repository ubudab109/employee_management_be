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
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if (!$user->hasVerifiedEmail()) {
                    return $request->wantsJson()
                        ? $this->sendError('Email Belum Terverifikasi', "Harap memverifikasi email Anda")
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
                    /* GET PERMISSIONS FROM PERMISSION SCOPEw */
                    foreach ($scope->permissions as $permission) {
                            $prm['id']              = $permission->id;
                            $prm['name']            = $permission->name;
                            $prm['display_name']    = $permission->display_name;
                            $prm['guard_name']      = $permission->guard_name;
                            $prm['order']           = $permission->order;
                            $prm['is_assigned']     = $user->roles[0]->hasPermissionTo($permission->name) ? true : false;
                            array_push($dataScope['permissions'], $prm);
                             // Count how many permission in current scope was assigned. If at least have one, then key 'is_scope_access' was true and if not then false
                            $dataScope['is_scope_access'] = arrFilterCount($dataScope['permissions'], 'is_assigned', true) > 0 ? true : false;
                    }

                    array_push($data, $dataScope);
                }
                return $this->sendResponse([
                    'token'         => $token,
                    'user_data'     => $user->makeHidden(['roles']),
                    'expired_token' => Date::now()->addDay(150),
                    'role'          => ucfirst($user->roles[0]->name),
                    'permissions'   => $data
                ],'Login Successfully');
            }

            return $this->sendUnauthorized('Kredensial Salah', null);
        } else {
            return $this->sendUnauthorized('Email atau Nomor Hp Tidak Ditemukan',null);
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
