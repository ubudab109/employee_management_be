<?php

namespace App\Http\Controllers\Web\UserManagement;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Services\ManagerServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserManagementController extends BaseController
{
    public $services;

    public function __construct(ManagerServices $services) 
    {
        $this->services = $services;
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
        $getData = $this->services->list($request);
        if ($getData['type'] == 'paginate') {
            $data = new PaginationResource($getData['data']);
        } else {
            $data = $getData['data'];
        }
        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * Detail User Management
     * @param \App\Models\UserManager $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError('Not Found','Data Not Found', Response::HTTP_NOT_FOUND);
        }
        return $this->sendResponse($data['data'], 'Data Fetched Successfully');
    }

    /**
     * Create or Invite User Management
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => 'required_without:email',
            'email'                => 'required_without:user_id',
            'name'                 => 'required_without:user_id',
            'nip'                  => 'required_without:user_id',
            'gender'               => 'required_without:user_id',
            'role'                 => 'required',
            'branch_id'            => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error', 'Please Select User');
        }

        $createUserManager = $this->services->store($request);
        if (!$createUserManager['status']) {
            if ($createUserManager['res'] == 422) {
                return $this->sendBadRequest($createUserManager['data'],null);
            } else {
                return $this->sendError(array('success' => 0),'Internal Server Error');
            }
        }

        return $this->sendResponse($createUserManager['data'], 'Data Created Successfully');
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
            'email'     => ['', Rule::unique('user_manager','email')->ignore($id) ],
            'name'      => '',
            'nip'       => ['', Rule::unique('user_manager','nip')->ignore($id) ],
            'gender'    => '',
            'role'      => '',
            'branch_id' => '',
        ]);
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }
        $updateUserManager = $this->services->update($request->all(), $id);
        if (!$updateUserManager['status']) {
            return $this->sendError('Internal Server Error');
        }
        return $this->sendResponse($updateUserManager['data'], 'User Updated Successfully');
    }

    /**
     * Remove or Cancel Invite User Management
     * @param \App\Models\UserManager $userId
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId)
    {
        $isDeleted = $this->services->destroy($userId);
        if (!$isDeleted) {
            return $this->sendError('Internal Server Error');
        }
        return $this->sendResponse(array('success' => 1), 'User Remove Succesfully');
    }

    /**
     * Resend Invite User Management
     * @param \App\Models\UserManager $userId
     * @return \Illuminate\Http\Response
     */
    public function resendInvitation($userId)
    {
        $resendInv = $this->services->resendInvite($userId);
        if (!$resendInv['status']) {
            return $this->sendError($resendInv['data']);
        }

        return $this->sendResponse($resendInv['data'], 'User Invited Successfully');
    }
}
