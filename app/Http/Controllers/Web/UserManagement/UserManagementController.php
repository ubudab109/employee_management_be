<?php

namespace App\Http\Controllers\Web\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Mail\UserManagerVerification;
use App\Models\User;
use App\Models\UserManager;
use App\Repositories\UserManagement\UserManagementInterface;
use App\Repositories\UserVerification\UserVerificationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends BaseController
{
    public $userManagement, $userVerification;

    /**
     * @param App\Repositories\UserManagement\UserManagementInterface $userManagement
     * @param App\Repositories\UserVerification\UserVerificationInterface $userVerification
     * @return null
     */
    public function __construct(UserManagementInterface $userManagement, UserVerificationInterface $userVerification)
    {
        $this->userManagement = $userManagement;
        $this->userVerification = $userVerification;
        $this->middleware('userpermissionmanager:user-management-permission-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:user-management-permission-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:user-management-permission-create',['only' => 'create']);
        $this->middleware('userpermissionmanager:user-management-permission-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:user-management-permission-delete',['only' => 'delete']);
        $this->middleware('userpermissionmanager:user-management-permission-resend',['only' => 'resendInvitation']);
    }

    /**
     * List User Management
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $data = $this->userManagement->getPaginateUserManagement($request->keyword, $request->status, $request->role, $request->show != null ? $request->show : 10);
        return $this->sendResponse(new PaginationResource($data), 'Data Fetched Successfully');
    }

    /**
     * Detail User Management
     * @param \App\Models\UserManager $id
     * @return \Illuminate\Http\Response
    */
    public function detail($id)
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

            $dataUserManager = [
                'name'              => $user->name,
                'email'             => $user->email,
                'nip'               => $user->nip,
                'gender'            => $user->gender,
                'phone_number'      => $user->phone_number,
                'password'          => Hash::make($password),
            ];
            $mailKey = generate_email_verification_key();
            
            $userManager = $this->userManagement->inviteUser($dataUserManager, $request->role, $mailKey);
            $dataEmail = [
                'name'      => $userManager->name,
                'email'     => $userManager->email,
                'password'  => $password,
                'role'      => $userManager->roles[0]->name,
                'key'       => $mailKey,
            ];
            Mail::to($userManager->email)->send(new UserManagerVerification($dataEmail));
            DB::commit();
            return $this->sendResponse($userManager, 'User Invited Successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => false), 'Internal Server Error');
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
        $validator = Validator::make($request->all(),[
            'role'  => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }
        $userManager = $this->userManagement->detailUserManagement($id);
        $userManager->roles()->detach();
        $userManager->assignRole($request->role);
        return $this->sendResponse($userManager, 'User Role Updated Successfully');
    }

    /**
     * Remove or Cancel Invite User Management
     * @param \App\Models\UserManager $userId
     * @return \Illuminate\Http\Response
    */
    public function delete($userId)
    {
        return $this->sendResponse($this->userManagement->deleteUserManagement($userId), 'User Remove Succesfully');
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
        Mail::to($userManager->email)->send(new UserManagerVerification($dataEmail));
        return $this->sendResponse($userManager, 'User Invited Successfully');
    }

    
}
