<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManager;
use App\Models\UserVerification;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class UserManagementRepository extends BaseRepository implements UserManagementInterface
{
    /**
    * @var ModelName
    */
    protected $model, $verification;

    public function __construct(UserManager $model, UserVerification $verification)
    {
      $this->model = $model;
      $this->verification = $verification;
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
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function($query) use ($keyword) {
        $query->where('name', 'LIKE', '%'.$keyword.'%')->orWhere('email', 'LIKE', '%'.$keyword.'%');
      })
      /* FILTER BY STATUS */
      ->when($status != null, function ($query) use ($status) {
        $query->where('invited_status', $status);
      })
      /* FILTER BY ROLE */
      ->when($role != '' || $role != null, function ($query) use ($role) {
        $query->whereHas('role', function ($roles) use ($role) {
          $roles->whereIn('name','LIKE','%'.$role.'%');
        });
      })->get();
    }

    /**
     * Detail User Management
     * @param int $userId
     * @return App\Models\UserManager
    */
    public function detailUserManagement($userId)
    {
      return $this->model->findOrFail($userId);
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
      /* FILTER BY KEYWORD */
      ->when($keyword != '' || $keyword != null, function($query) use ($keyword) {
        $query->where('name', 'LIKE', '%'.$keyword.'%')->orWhere('email', 'LIKE', '%'.$keyword.'%');
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
    public function inviteUser(array $data, $role, $code)
    {
      $user = $this->model->create($data);
      $user->assignRole($role);
      $user->verification()->create([
        'code'              => $code,
        'expired_at'        => date('Y-m-d', strtotime('+1 days')),
        'verification_type' => 'email',
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
      return $user->syncRoles($role);
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
}
