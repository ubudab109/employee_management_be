<?php

namespace App\Repositories\RolePermissionManager;

interface RolePermissionManagerInterface
{
  public function listRoleManager($keyword);
  public function detailRoleManager($roleId);
  public function listAllPermissionScope();
  public function listAllPermissionFromScope();
  public function listAllPermission();
  public function listRolePermissionWithoutScope($roleId);
  public function createRolePermission(array $data, array $permissionData);
  public function updateRolePermission($roleId, $roleName, array $permissionData);
  public function deleteRolePermission($roleId);
}