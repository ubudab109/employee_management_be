<?php

namespace App\Repositories\RolePermissionManager;

use App\Models\Permission;
use App\Models\PermissionScope;
use App\Models\Role;


class RolePermissionManagerRepository implements RolePermissionManagerInterface
{
    /**
    * @var App\Models\Role
    * @var App\Models\Permission
    * @var App\Models\PermissionScope
    */
    protected $role, $permission, $permissionScope;

    public function __construct(Role $role, Permission $permission, PermissionScope $permissionScope)
    {
      $this->role = $role;
      $this->permission = $permission;
      $this->permissionScope = $permissionScope;
    }

    /**
     * List Role Manager
     * @param String $keyword
     * @return App\Models\Role
     */
    public function listRoleManager($keyword)
    {
      return $this->role
      ->where('is_role_manager', true)
      /* SEARCH KEYWORD */
      ->when($keyword != '' || $keyword != null, function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%'.$keyword.'%');
      })
      ->get();
    }
    
    /**
     * Detail Role Manager
     * @param App\Models\Role $roleId
     * @return App\Models\Role
    */
    public function detailRoleManager($roleId)
    {
      return $this->role->findOrFail($roleId);
    }

    /**
     * List Permission Scope
     * @return App\Models\PermissionScope
    */
    public function listAllPermissionScope()
    {
      return $this->permissionScope->orderBy('order')->get();
    }


    /**
     * List Permission from Permission Scope
     * @return App\Models\PermissionScope
    */
    public function listAllPermissionFromScope()
    {
      return $this->permissionScope->orderBy('order')->with('permissions')->get();
    }

    /**
     * List Permission Role without Permission Scope
     * @param App\Models\Permission $roleId
     * @return App\Models\PermissionScope
    */
    public function listRolePermissionWithoutScope($roleId) 
    {
      $role = $this->role->findOrFail($roleId);
      return $role->permissions()->pluck('id');
    }
    /**
     * List All Permissions
     * @return App\Models\Permission
    */
    public function listAllPermission()
    {
      return $this->permission->get();
    }

    /**
     * Create Role and Permission Manager
     * @param array $data for Role
     * @param array $permissionData for attach permission to role
     * @return App\Models\Role
    */
    public function createRolePermission(array $data, $permissionData)
    {
      $roleData = $this->role->create($data);
      $roleData->givePermissionTo($permissionData);
      return $this->role->findOrFail($roleData->id);
    }

    /**
     * Update Role and Permission Manager
     * @param App\Models\Role $roleId
     * @param String $roleName
     * @param array $permissionData for sync permission to role
     * @return App\Models\Role
    */
    public function updateRolePermission($roleId, $roleName, array $permissionData)
    {
      $roleData = $this->role->findOrFail($roleId);
      $roleData->update([
        'name' => $roleName,
      ]);
      $roleData->syncPermissions($permissionData);

      return $this->role->findOrFail($roleId);
    }

    /**
     * Delete Role and Permission Manager
     * @param App\Models\Role $roleID
     * @return App\Models\Role
    */
    public function deleteRolePermission($roleId)
    {
       return $this->role->findOrFail($roleId)->delete();
    }
}