<?php

namespace App\Services;

use App\Repositories\CompanyBranch\CompanyBranchInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class RoleServices
{
    public $roleInterface, $companyBranch;

    public function __construct(RolePermissionManagerInterface $roleInterface, CompanyBranchInterface $companyBranch)
    {
        $this->roleInterface = $roleInterface;
        $this->companyBranch = $companyBranch;
    }

    /**
     * It returns a list of roles from the roleInterface
     * 
     * @param array $param - parameter to filter and search
     * 
     * @return Collection return value is the result of the listRoleManager method of the roleInterface
     * object.
     */
    public function list($param = [])
    {
        return $this->roleInterface->listRoleManager(isset($param['keyword']) ? $param['keyword'] : null, isset($param['branch_id']) ? $param['branch_id'] : null);
    }

    /**
     * This function returns a list of all permissions from the scope of the role.
     * 
     * @return array list of all permissions from the scope.
     */
    public function listPermissionsFromScope()
    {
        return $this->roleInterface->listAllPermissionFromScope();
    }

    /**
     * The function will return all permissions with scope and also return all permissions without scope
     * 
     * @param int $roleId - The role id that you want to get the permissions for.
     * 
     * @return object
     */
    public function detailWithPermissions($roleId)
    {
        $role = $this->roleInterface->detailRoleManager($roleId);

        /* ALL DATA WILL ASSIGN HERE */
        $data = array();

        /* GET SCOPE PERMISSION FIRST */
        $scopes = $this->roleInterface->listAllPermissionScope();
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

        return [
            'is_current_role_user'  => currentUserRole('sanctum:manager') === $role->name,
            'role'                  => $role->name,
            'role_permissions'      => $data,
            'permissions'           => $this->roleInterface->listRolePermissionWithoutScope($role->id)
        ];
    }

    /**
     * I'm trying to create a role and permission for the role
     * 
     * @param array $data - data for input role and permissions
     * 
     * @return object
     */
    public function createRolePermissions($data = [])
    {
        DB::beginTransaction();
        try {
            $branch = branchIdForCreateData(isSuperAdmin(), isset($data['branch_id']) && $data['branch_id'] != null ? $data['branch_id'] : null);

            $dataRole = [
                'name'              => $data['name'],
                'is_role_manager'   => true,
                'guard_name'        => 'sanctum:manager',
                'branch_id'         => $branch,
            ];
            $permissionData = [
                'dashboard-page',
            ];

            array_push($permissionData, $data['permissions']);
            $role = $this->roleInterface->createRolePermission($dataRole, $permissionData);
            DB::table('user_manager_activities')->insert([
                'user_manager_id'       => Auth::user()->id,
                'activities'             => CREATE_ROLE_PERMISSION,
                'date'                  => Date::now(),
                'time'                  => date('H:i'),
            ]);
            DB::commit();
            return [
                'status'    => true,
                'data'      => $role,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'data'      => null,
            ];
        }
    }

    /**
     * The function will update the role permission, and then return the data of the role permission
     * 
     * @param array $data - Request data for update
     * @param int $roleId - the id of the role
     * 
     * @return object
     */
    public function updateRolePermission($data = [], $roleId)
    {
        DB::beginTransaction();
        try {
            $role = $this->roleInterface->detailRoleManager($roleId);
            $this->roleInterface->updateRolePermission($roleId, $data['name'], $data['permissions']);

            /* ALL DATA PERMISSIONS WILL ASSIGN HERE */
            $data = array();
            $data['is_current_role_user'] = Auth::guard('sanctum:manager')->user()->getRoleAttribute() === ucfirst($role->name);
            $data['data'] = [];
            /* GET SCOPE PERMISSION FIRST */
            $scopes = $this->roleInterface->listAllPermissionScope();

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
            DB::commit();
            return [
                'status'    => true,
                'data'      => $data,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'data'      => $err->getMessage(),
            ];
        }
    }

    /**
     * It deletes a role and all the permissions associated with it.
     * 
     * @param int roleId The id of the role you want to delete
     * 
     * @return bool boolean with a status of true or false.
     */
    public function deleteRole($roleId)
    {
        DB::beginTransaction();
        try {
            $this->roleInterface->deleteRolePermission($roleId);
            DB::commit();
            return true;
        } catch (\Exception $err) {
            DB::rollBack();
            return false;
        }
    }
}
