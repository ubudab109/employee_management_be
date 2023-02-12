<?php

namespace App\Repositories\UserManagement;

interface UserManagementInterface
{
  public function getUserManagement($keyword, $status, $role, $branch = null);
  public function getPaginateUserManagement($keyword, $status, $role, $show, $branch = null);
  public function detailUserManagement($userId);
  public function detailUserBranchAssign($userId);
  public function verifyEmailUserManagement($userId);
  public function checkExistsUserByEmail($email);
  public function inviteUser(array $data, $branchId, $role, $code);
  public function updateUser(array $data, $managerId);
  public function changeRole($userId, $role);
  public function deleteUserManagement($userId);
  public function moveBranch($userId, $branchId);
}