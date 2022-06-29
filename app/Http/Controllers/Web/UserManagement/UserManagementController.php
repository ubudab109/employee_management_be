<?php

namespace App\Http\Controllers\Web\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Jobs\SendEmailJob;
use App\Mail\UserManagerVerification;
use App\Models\User;
use App\Models\UserManager;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use App\Repositories\UserManagement\UserManagementInterface;
use App\Repositories\UserVerification\UserVerificationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends BaseController
{
    public $userManagement, $userVerification,$roles;

    /**
     * @param App\Repositories\UserManagement\UserManagementInterface $userManagement
     * @param App\Repositories\UserVerification\UserVerificationInterface $userVerification
     * @return null
     */
    public function __construct(
        UserManagementInterface $userManagement,
        UserVerificationInterface $userVerification,
        RolePermissionManagerInterface $roles
    ) {
        $this->userManagement = $userManagement;
        $this->userVerification = $userVerification;
        $this->roles = $roles;
        $this->middleware('userpermissionmanager:user-management-permission-list', ['only' => 'index']);
        $this->middleware('userpermissionmanager:user-management-permission-detail', ['only' => 'detail']);
        $this->middleware('userpermissionmanager:user-management-permission-create', ['only' => 'create']);
        $this->middleware('userpermissionmanager:user-management-permission-update', ['only' => 'update']);
        $this->middleware('userpermissionmanager:user-management-permission-delete', ['only' => 'delete']);
        $this->middleware('userpermissionmanager:user-management-permission-resend', ['only' => 'resendInvitation']);
    }

    /**
     * List User Management
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->show != null && $request->show != 'all') {
            $data = $this->userManagement->getPaginateUserManagement($request->keyword, $request->status, $request->role, $request->show != null ? $request->show : 10, $request->branch_id);
            $res = new PaginationResource($data);
        } else {
            $res = $this->userManagement->getUserManagement($request->keyword, $request->status, $request->role, $request->branch_id);
        }
        return $this->sendResponse($res, 'Data Fetched Successfully');
    }

    /**
     * Detail User Management
     * @param \App\Models\UserManager $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->sendResponse($this->userManagement->detailUserManagement($id), 'Data Fetched Successfully');
    }

    /**
     * Create or Invite User Management
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => 'required',
            'role'                 => 'required',
            'branch_id'            => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error', 'Please Select User');
        }

        DB::beginTransaction();
        try {
            $user = User::find($request->user_id);
            $password = randomPassword();
            $checkUser = $this->userManagement->checkExistsUserByEmail($user->email);

            if ($checkUser) {
                return $this->sendBadRequest('This User Has Been Invited Before');
            }

            $branch = branchIdForCreateData(isSuperAdmin(), $request->has('branch_id') ? $request->branch_id : null);

            $dataUserManager = [
                'name'              => $user->name,
                'email'             => $user->email,
                'nip'               => $user->nip,
                'gender'            => $user->gender,
                'phone_number'      => $user->phone_number,
                'password'          => Hash::make($password),
            ];
            $mailKey = generate_email_verification_key();

            $this->userManagement->inviteUser($dataUserManager, $branch, $request->role, $mailKey);
            $dataEmail = [
                'name'      => $user->name,
                'email'     => $user->email,
                'password'  => $password,
                'role'      => $this->roles->detailRoleManager($request->role)->name,
                'key'       => $mailKey,
            ];
            dispatch(new SendEmailJob($dataEmail));
            DB::commit();
            return $this->sendResponse($user, 'User Invited Successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => false), $err->getMessage().'-'.$err->getLine());
        }
    }

    /**
     * Update Role User Management
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\UserManager $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role'      => '',
            'branch_id' => '',
        ]);
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }
        $userManager = $this->userManagement->detailUserBranchAssign($id);

        if ($request->role != null) {
            $userManager->roles()->detach();
            $userManager->assignRole($request->role);
        }

        if ($request->branch_id != null) {
            $userManager->update([
                'branch_id' => $request->branch_id,
            ]);
        }

        return $this->sendResponse(array('success' => 1), 'User Updated Successfully');
    }

    /**
     * Remove or Cancel Invite User Management
     * @param \App\Models\UserManager $userId
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId)
    {
        return $this->sendResponse(array('success' => $this->userManagement->deleteUserManagement($userId)), 'User Remove Succesfully');
    }

    /**
     * Resend Invite User Management
     * @param \App\Models\UserManager $userId
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation($userId)
    {
        $userManager = $this->userManagement->detailUserManagement($userId);
        $password = randomPassword();
        $mailKey = generate_email_verification_key();
        $this->userVerification->deleteVerificationEmail(UserManager::class, $userId);
        $dataEmail = [
            'name'      => $userManager->name,
            'email'     => $userManager->email,
            'password'  => $password,
            'role'      => $userManager->roles[0]->name,
            'key'       => $mailKey,
        ];
        $this->userVerification->generateEmailVerification(UserManager::class, $userId, $mailKey);
        dispatch(new SendEmailJob($dataEmail));
        return $this->sendResponse($userManager, 'User Invited Successfully');
    }
}
