<?php

namespace App\Http\Controllers\Web\RolePermission;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyBranch\CompanyBranchInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends BaseController
{
    public $rolePermission, $companyBranchInterface;

    public function __construct(RolePermissionManagerInterface $rolePermission, CompanyBranchInterface $companyBranchInterface)
    {
        $this->rolePermission = $rolePermission;
        $this->companyBranchInterface = $companyBranchInterface;
        // $this->middleware('userpermissionmanager:role-permission-list',['only' => 'listRole']);
        // $this->middleware('userpermissionmanager:role-permission-detail',['only' => 'detailRoleWithPermissions']);
        // $this->middleware('userpermissionmanager:role-permission-create',['only' => 'createRolePermissions']);
        // $this->middleware('userpermissionmanager:role-permission-update',['only' => 'updateRolePermissions']);
        // $this->middleware('userpermissionmanager:role-permission-delete',['only' => 'deleteRole']);
        // $this->middleware('userpermissionmanager:role-permission-delete',['only' => 'deleteRole']);
    }

    /**
     * List Role Manager
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function listRole(Request $request)
    {
        return $this->sendResponse($this->rolePermission->listRoleManager($request->keyword, $request->branch_id), 'Data Fetched Successfully');
    }

    /**
     * List All Permission Scope and Permissions
     * @return Illuminate\Http\Response
     */
    public function listPermissions()
    {
        return $this->sendResponse($this->rolePermission->listAllPermissionFromScope(), 'Data Fetched Successfully');
    }

    /**
     * Detail role with permissions
     * @return Illuminate\Http\Response
     */
    public function detailRoleWithPermissions($roleId)
    {
        $role = $this->rolePermission->detailRoleManager($roleId);

        /* ALL DATA WILL ASSIGN HERE */
        $data = array();

        /* GET SCOPE PERMISSION FIRST */
        $scopes = $this->rolePermission->listAllPermissionScope();
        // return $scopes;
        foreach ($scopes as $scope) {
            $dataScope['id'] = $scope->id;
            $dataScope['name'] = $scope->name;
            $dataScope['order'] = $scope->order;
            $dataScope['permissions'] = array();

            /* GET PERMISSIONS FROM PERMISSION SCOPE */
            foreach ($scope->permissions as $permission) {
                $prm['id']              = $permission->id;
                $prm['name']            = $permission->name;
                $prm['display_name']    = $permission->display_name;
                $prm['guard_name']      = $permission->guard_name;
                $prm['order']           = $permission->order;
                $prm['scope_id']        = $permission->permissionScope->id;

                
                $prm['selected']        = $role->hasPermissionTo($permission->name) ? true : false;


                array_push($dataScope['permissions'], $prm);

                // Count how many permission in current scope was assigned. If equal to total permission in current scope then key 'is_scope_assigned' was true and if not then false
                $dataScope['selected'] = arrFilterCount($dataScope['permissions'], 'selected', true) == count($scope->permissions) ? true : false;
            }

            array_push($data, $dataScope);
        }

        return $this->sendResponse([
            'is_current_role_user'  => currentUserRole('sanctum:manager') === $role->name,
            'role'                  => $role->name,
            'role_permissions'      => $data,
            'permissions'           => $this->rolePermission->listRolePermissionWithoutScope($role->id)
        ], 'Data Fetched Successfully');
    }

    /**
     * Create Role and Permission Manager
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function createRolePermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'permissions'   => 'array',
            'branch_id'     => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $branch = branchIdForCreateData(isSuperAdmin(), $request->has('branch_id') ? $request->branch_id : null);

            $dataRole = [
                'name'              => $request->name,
                'is_role_manager'   => true,
                'guard_name'        => 'sanctum:manager',
                'branch_id'         => $branch,
            ];
            $permissionData = [
                'dashboard-page',
            ];

            array_push($permissionData, $request->permissions);
            $role = $this->rolePermission->createRolePermission($dataRole, $permissionData);
            DB::table('user_manager_activities')->insert([
                'user_manager_id'       => Auth::user()->id,
                'activities'             => CREATE_ROLE_PERMISSION,
                'date'                  => Date::now(),
                'time'                  => date('H:i'),
            ]);
            DB::commit();
            return $this->sendResponse($role, 'Data Created Successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => 0), $err->getMessage() . '' . $err->getLine());
        }
    }

    /**
     * Update Role and Permission Manager
     * @param Illuminate\Http\Request
     * @param App\Models\Role $id
     * @return Illuminate\Http\Response
     */
    public function updateRolePermissions(Request $request, $roleId)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'permissions'   => 'array',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        $role = $this->rolePermission->detailRoleManager($roleId);
        $this->rolePermission->updateRolePermission($roleId, $request->name, $request->permissions);

        /* ALL DATA PERMISSIONS WILL ASSIGN HERE */
        $data = array();
        $data['is_current_role_user'] = auth()->user()->roles[0]->name === $role->name;
        $data['data'] = [];
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
                $prm['is_assigned']     = $role->hasPermissionTo($permission->name) ? true : false;
                array_push($dataScope['permissions'], $prm);
                // Count how many permission in current scope was assigned. If at least have one, then key 'is_scope_access' was true and if not then false
                $dataScope['is_scope_access'] = arrFilterCount($dataScope['permissions'], 'is_assigned', true) > 0 ? true : false;
            }

            array_push($data['data'], $dataScope);
        }

        return $this->sendResponse($data, 'Data Updated Successfully');
    }

    /**
     * Delete Role and Permission Manager
     * @param App\Models\Role $id
     * @return Illuminate\Http\Response
     */
    public function deleteRole($roleId)
    {
        $this->rolePermission->deleteRolePermission($roleId);
        return $this->sendResponse($this->rolePermission->listRoleManager('', null), 'Data Deleted Successfully');
    }
}
