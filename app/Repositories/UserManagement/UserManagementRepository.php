<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManager;
use App\Models\UserManagerAssign;
use App\Models\UserVerification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
  public function getUserManagement($keyword, $status, $role, $branch)
  {
    if (!$this->isSuperAdmin) {
      $data = $this->model
      ->whereHas('branchAssign', function ($subQuery) {
        $subQuery->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      ->with('branchAssign');
    } else {
      $data = $this->model
      ->where('id', '!=', Auth::guard('sanctum:manager')->user()->id)
      ->when($branch != null, function ($query) use ($branch) {
        $query->whereHas('branchAssign', function ($subQuery) use ($branch) {
          $subQuery->where('branch_id', $branch);
        });
      })
      ->with('branchAssign');
    }
    return $data
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function ($query) use ($keyword) {
        $query->where("name", 'LIKE', '%'. $keyword .'%');
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
    $data = $this->model->with('branchAssign')->find($userId);
    if ($data) {
      return $data;
    } else {
      return null;
    }
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
  public function getPaginateUserManagement($keyword, $status, $role, $show, $branch = null)
  {
    return $this->model
      ->with('branchAssign')
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->whereHas('branchAssign', function ($subQuery) {
          $subQuery->where('branch_id', branchSelected('sanctum:manager')->id);
        });
      })
      ->when($this->isSuperAdmin, function ($query) use ($branch) {
        $query->when($branch != null, function ($subQuery) use ($branch) {
          $subQuery->whereHas('branchAssign', function ($subQuery) use ($branch) {
            $subQuery->where('branch_id', $branch);
          });
        });
      })
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function ($query) use ($keyword) {
        $query->where("name", 'LIKE', '%'. $keyword .'%');
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
   * It takes an array of data and a managerId, finds the managerId in the database, updates the data,
   * and returns the updated user
   * 
   * @param array data an array of the data you want to update
   * @param int managerId The id of the user you want to update
   * 
   * @return object user object.
   */
  public function updateUser(array $data, $managerId)
  {
    $user = $this->model->find($managerId);
    $user->update($data);
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
    $userBranch = $user->branch()->first();
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
