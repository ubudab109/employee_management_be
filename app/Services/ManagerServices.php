<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Models\UserManager;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use App\Repositories\UserManagement\UserManagementInterface;
use App\Repositories\UserVerification\UserVerificationInterface;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ManagerServices
{
    public $userManagement, $userVerification,$roles;

    public function __construct(
        UserManagementInterface $userManagement,
        UserVerificationInterface $userVerification,
        RolePermissionManagerInterface $roles
    )
    {
        $this->userManagement = $userManagement;
        $this->userVerification = $userVerification;
        $this->roles = $roles;
    }

    /**
     * It will return a list of users based on the parameters passed to it
     * 
     * @param array param - Parameter to filter
     * @return object 
     */
    public function list($param = [])
    {
        if (isset($param['show']) && $param['show'] != 'all') {
            $data = $this->userManagement->getPaginateUserManagement(
                isset($param['keyword']) && $param['keyword'] != null ? $param['keyword'] : null,
                isset($param['status']) && $param['status'] != null ? $param['status'] : null,
                isset($param['role']) && $param['role'] != null ? $param['role'] : null,
                $param['show'] == null ? 10 : $param['show'],
                isset($param['branch_id']) && $param['branch_id'] != null ? $param['branch_id'] : null,
            );
            $type = 'paginate';
        } else {
            $data = $this->userManagement->getUserManagement(
                isset($param['keyword']) && $param['keyword'] != null ? $param['keyword'] : null,
                isset($param['status']) && $param['status'] != null ? $param['status'] : null,
                isset($param['role']) && $param['role'] != null ? $param['role'] : null,
                isset($param['branch_id']) && $param['branch_id'] != null ? $param['branch_id'] : null,
            );
            $type = 'list';
        }
        return [
            'status'    => true,
            'type'      => $type,
            'data'      => $data, 
        ];
    }

    /**
     * It returns an array with a status and data key
     * 
     * @param int id The id of the user you want to get the details of.
     * 
     * @return object object with two keys, status and data.
     */
    public function detail($id)
    {
        $data = $this->userManagement->detailUserManagement($id);
        if (is_null($data)) {
            return [
                'status'    => false,
                'data'      => $data,
            ];
        }

        return [
            'status'    => true,
            'data'      => $data,
        ];
    }

    /**
     * The function is used to create a new user, and the user will be sent an email containing the
     * password
     * 
     * @param array data - the data that will be passed to the job's handle method
     * @return object
     */
    public function store($data = [])
    {
        DB::beginTransaction();
        try {
            $password = randomPassword();
            if (isset($data['user_id'])) {
                $user = User::find($data['user_id']);
                $checkUser = $this->userManagement->checkExistsUserByEmail($user->email);
    
                if ($checkUser) {
                    return [
                        'status'    => false,
                        'data'      => 'This User Had Been Invited Before',
                        'res'       => 422,
                    ];
                }
                $dataUserManager = [
                    'name'              => $user->firstname.' '.!is_null($user->lastname) ? $user->lastname : '',
                    'email'             => $user->email,
                    'nip'               => $user->nip,
                    'gender'            => $user->gender,
                    'phone_number'      => $user->phone_number,
                    'password'          => Hash::make($password),
                ];
            } else {
                $checkUser = $this->userManagement->checkExistsUserByEmail($data['email']);
    
                if ($checkUser) {
                    return [
                        'status'    => false,
                        'data'      => 'This User Had Been Invited Before',
                        'res'       => 422,
                    ];
                }
                $dataUserManager = [
                    'name'              => $data['name'],
                    'email'             => $data['email'],
                    'nip'               => $data['nip'],
                    'gender'            => $data['gender'],
                    'password'          => Hash::make($password),
                ];
            }

            $branch = branchIdForCreateData(isSuperAdmin(), $data['branch_id'] ? $data['branch_id'] : null);
            /**
             * CHECKING ROLE IN CURRENT SELECTED BRANCH (ONLY FOR SUPERADMIN)
             * IF CURRENT ROLE NOT EXISTS THEN WILL CREATE A NEW ROLE WHICH MEAN A HEAD BRANCH
             * ELSE, WILL GET THE ROLE BY ID
             */
            $roleBranch = DB::table('roles')->where('branch_id', $data['branch_id'])->exists();
            if (!$roleBranch) {
                $branchName = DB::table('company_branch')->where('id', $data['branch_id'])->first()->branch_name;
                $role = DB::table('roles')->insertGetId([
                    'name'            => 'Head Branch - '. $branchName,
                    'guard_name'      => 'sanctum:manager',
                    'is_role_manager' => true,
                    'is_headbranch'   => true,
                    'branch_id'       => $branch,
                    'created_at'      => Date::now(),
                    'updated_at'      => Date::now(),  
                ]);
            } else {
                $role = $data['role'];
            }

            $mailKey = generate_email_verification_key();

            $userManager = $this->userManagement->inviteUser($dataUserManager, $branch, $role, $mailKey);
            $dataEmail = [
                'name'      => $dataUserManager['name'],
                'email'     => $dataUserManager['email'],
                'password'  => $password,
                'role'      => $this->roles->detailRoleManager($role)->name,
                'key'       => $mailKey,
            ];
            dispatch(new SendEmailJob($dataEmail, USER_MANAGER_TYPE));
            DB::commit();
            return [
                'status'   => true,
                'data'     => $userManager,
                'res'      => 200,
            ];
        } catch (\Exception $err) {
            Log::info($err);
            DB::rollBack();
            return [
                'status' => false,
                'data'   => null,
                'res'    => 500,   
            ];
        }
    }

    /**
     * It updates the user manager data
     * Including role and branch
     * 
     * @param array $data - array data to input data to database
     * @param int $managerId - The id of the manager to be updated
     * 
     * @return object - user manager data object.
     */
    public function update($data = [], $managerId)
    {
        DB::beginTransaction();
        try {
            $manager = $this->userManagement->updateUser($data, $managerId);
            if (isset($data['role']) && !is_null($data['role'])) {
                $this->userManagement->changeRole($managerId, $data['role']);
            }
    
            if (isset($data['branch_id']) && !is_null($data['branch_id'])) {
                $manager->branch()->update([
                    'branch_id' => $data['branch_id'],
                ]);
            }
            DB::commit();
            return [
                'status'    => true,
                'data'      => $manager,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
        Log::info($err->getMessage());
            return [
                'status'    => false,
                'data'      => null,
            ];
        }
    }

    /**
     * It deletes a user from the database
     * 
     * @param int $managerId The id of the manager to be deleted
     * 
     * @return boolean boolean value.
     */
    public function destroy($managerId)
    {
        return $this->userManagement->deleteUserManagement($managerId);
    }

    /**
     * Delete the old email verification key and generate a new one
     * 
     * @param int managerId the id of the user
     * @return object
     */
    public function resendInvite($managerId)
    {
        DB::beginTransaction();
        try {
            $userManager = $this->userManagement->detailUserManagement($managerId);
            $password = randomPassword();
            $mailKey = generate_email_verification_key();
            $this->userVerification->deleteVerificationEmail(UserManager::class, $managerId);
            $dataEmail = [
                'name'      => $userManager->name,
                'email'     => $userManager->email,
                'password'  => $password,
                'role'      => $userManager->getRoleAttribute(),
                'key'       => $mailKey,
            ];
            $this->userVerification->generateEmailVerification(UserManager::class, $managerId, $mailKey);
            dispatch(new SendEmailJob($dataEmail, USER_MANAGER_TYPE));
            DB::commit();
            return [
                'status'    => true,
                'data'      => $userManager,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'data'      => $err->getMessage().'-'.$err->getLine(),   
            ];
        }
        
    }
}