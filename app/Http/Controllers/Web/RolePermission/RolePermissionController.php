<?php

namespace App\Http\Controllers\Web\RolePermission;

use App\Http\Controllers\BaseController;
use App\Services\RoleServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolePermissionController extends BaseController
{
    public $services;

    public function __construct(RoleServices $services)
    {
        $this->services = $services;
        if (config('app.env') != 'development') { 
            $this->middleware('userpermissionmanager:role-permission-list',['only' => 'listRole']);
            $this->middleware('userpermissionmanager:role-permission-detail',['only' => 'detailRoleWithPermissions']);
            $this->middleware('userpermissionmanager:role-permission-create',['only' => 'createRolePermissions']);
            $this->middleware('userpermissionmanager:role-permission-update',['only' => 'updateRolePermissions']);
            $this->middleware('userpermissionmanager:role-permission-delete',['only' => 'deleteRole']);
            $this->middleware('userpermissionmanager:role-permission-delete',['only' => 'deleteRole']);
        }
    }

    /**
     * List Role Manager
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
     */
    public function listRole(Request $request)
    {
        return $this->sendResponse($this->services->list($request), 'Data Fetched Successfully');
    }

    /**
     * List All Permission Scope and Permissions
     * @return Illuminate\Http\Response
     */
    public function listPermissions()
    {
        return $this->sendResponse($this->services->listPermissionsFromScope(), 'Data Fetched Successfully');
    }

    /**
     * Detail role with permissions
     * @return Illuminate\Http\Response
     */
    public function detailRoleWithPermissions($roleId)
    {
        return $this->sendResponse($this->services->detailWithPermissions($roleId), 'Data Fetched Successfully');
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

        $createRolePermission = $this->services->createRolePermissions($request);
        if (!$createRolePermission['status']) {
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
        return $this->sendResponse($createRolePermission['data'], 'Data Created Successfully');
        
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

        $updateRolePermission = $this->services->updateRolePermission($request, $roleId);
        if (!$updateRolePermission['status']) {
            return $this->sendError(array('success' => 0), $updateRolePermission['data']);
        }
        return $this->sendResponse($updateRolePermission['data'], 'Data Created Successfully');
    }

    /**
     * Delete Role and Permission Manager
     * @param App\Models\Role $id
     * @return Illuminate\Http\Response
     */
    public function deleteRole($roleId)
    {
        $isDeleted = $this->services->deleteRole($roleId);
        if (!$isDeleted) {
            return $this->sendBadRequest(array('success' => 0), 'Internal Server Error');
        }
        return $this->sendResponse($this->services->list([]), 'Data Deleted Successfully');
    }
}
