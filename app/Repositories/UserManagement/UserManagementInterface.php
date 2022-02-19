<?php

namespace App\Repositories\UserManagement;

interface UserManagementInterface
{
  public function getUserManagement($keyword, $status, $role);
  public function getPaginateUserManagement($keyword, $status, $role, $show);
  public function detailUserManagement($userId);
  public function verifyEmailUserManagement($userId);
  public function checkExistsUserByEmail($email);
  public function inviteUser(array $data, $role, $code);
  public function changeRole($userId, $role);
  public function deleteUserManagement($userId);
}