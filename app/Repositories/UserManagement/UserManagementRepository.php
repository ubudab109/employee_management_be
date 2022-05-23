<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManager;
use App\Models\UserManagerAssign;
use App\Models\UserVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;

class UserManagementRepository implements UserManagementInterface
{
  /**
   * @var ModelName
   */
  protected $model, $verification, $userManagerAssign, $isSuperAdmin;

  public function __construct(UserManager $model, UserVerification $verification, UserManagerAssign $userManagerAssign)
  {
    $this->isSuperAdmin = isSuperAdmin();
    $this->model = $model;
    $this->verification = $verification;
    $this->userManagerAssign = $userManagerAssign;
  }

  /**
   * Get User Manager List
   * Not Paginate
   * @param String $keyword
   * @param int $status
   * @param String $role
   * @return App\Models\UserManager
   */
  public function getUserManagement($keyword, $status, $role)
  {
    return $this->model
      ->whereHas('branchAssign')
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->whereHas('branchAssign', function ($subQuery) {
          $subQuery->where('branch_id', branchManagerSelected('sanctum:manager')->id);
        });
      })
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%' . $keyword . '%')->orWhere('email', 'LIKE', '%' . $keyword . '%');
      })
      /* FILTER BY STATUS */
      ->when($status != null, function ($query) use ($status) {
        $query->where('invited_status', $status);
      })
      /* FILTER BY ROLE */
      ->when($role != null, function (Builder $query) use ($role) {
        $query->role($role);
      })->get();
  }

  /**
   * Detail User Management
   * @param int $userId
   * @return App\Models\UserManager
   */
  public function detailUserManagement($userId)
  {
    return $this->model->with('branchAssign')->findOrFail($userId);
  }

  /**
   * Detail of Branch User Management
   * @param int $userId
   * @return App\Models\UserManager
   */
  public function detailUserBranchAssign($userId)
  {
    $user = $this->model->findOrFail($userId);
    return $user->branch()->first();
  }

  /**
   * Update Email Verification Status User Manager
   * @param int $userId
   * @return App\Models\UserManager
   */
  public function verifyEmailUserManagement($userId)
  {
    return $this->model->findOrFail($userId)->update([
      'email_verified_at' => Date::now(),
    ]);
  }

  /**
   * Get User Manager List
   * Paginate
   * @param String $keyword
   * @param int $status
   * @param String $role
   * @param int $show
   * @return App\Models\UserManager
   */
  public function getPaginateUserManagement($keyword, $status, $role, $show)
  {
    return $this->model
      ->whereHas('branchAssign')
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->whereHas('branchAssign', function ($subQuery) {
          $subQuery->where('branch_id', branchManagerSelected('sanctum:manager')->id);
        });
      })
      ->whereHas('branchAssign', function ($query) {
        $query->where('branch_id', branchManagerSelected('manager')->id);
      })
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%' . $keyword . '%')->orWhere('email', 'LIKE', '%' . $keyword . '%');
      })
      /* FILTER BY STATUS */
      ->when($status != null, function ($query) use ($status) {
        $query->where('invited_status', $status);
      })
      /* FILTER BY ROLE */
      ->when($role != null, function (Builder $query) use ($role) {
        $query->role($role);
      })->paginate($show);
  }

  /**
   * Invite User Manager
   * @param array $data to invite
   * @return App\Models\UserManager
   */
  public function inviteUser(array $data, $branchId, $role, $code)
  {
    $user = $this->model->create($data);
    $user->branch()->create([
      'branch_id' => $branchId
    ]);
    $userManagerBranch = $this->userManagerAssign->where([
      'user_manager_id' => $user->id,
      'branch_id'       => $branchId,
    ])->first();

    $userManagerBranch->assignRole($role);
    $user->verification()->create([
      'code'              => $code,
      'expired_at'        => date('Y-m-d', strtotime('+1 days')),
      'verification_type' => EMAIL_VERIFICATION_TYPE,
    ]);
    return $user;
  }

  /**
   * Check User Exists By Email
   * @param String $email
   * @return App\Models\UserManager
   */
  public function checkExistsUserByEmail($email)
  {
    return $this->model->where('email', $email)->exists();
  }

  /**
   * Change Role User Manager
   * @param int App\Models\UserManager $id
   * @param String $role role name
   * @return App\Models\UserManager
   */
  public function changeRole($userId, $role)
  {
    $user = $this->model->findOrFail($userId);
    $userBranch = $user->branchAssign()->first();
    return $userBranch->syncRoles($role);
  }

  /**
   * Delete User Manager
   * @param int App\Models\UserManager $id
   * @return App\Models\UserManager
   */
  public function deleteUserManagement($userId)
  {
    return $this->model->findOrFail($userId)->delete();
  }

  /**
   * Move current user branch
   * 
   * @param int App\Models\UserManager $id - ID of user manager
   * @param int App\Models\CompanyBranch $id - ID of company branch
   * @return boolean
   */
  public function moveBranch($userId, $branchId)
  {
    $user = $this->model->findOrFail($userId);
    return $user->branchAssign()->first()->update([
      'branch_id' => $branchId,
    ]);
  }
}
