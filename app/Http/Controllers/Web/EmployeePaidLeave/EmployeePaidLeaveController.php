<?php

namespace App\Http\Controllers\Web\EmployeePaidLeave;

use App\Http\Controllers\BaseController;
use App\Services\EmployeeLeaveServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeePaidLeaveController extends BaseController
{
    public $services;

    public function __construct(EmployeeLeaveServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:employee-leave-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:employee-leave-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:employee-leave-delete',['only' => 'delete']);
        $this->middleware('userpermissionmanager:employee-leave-update',['only' => 'update']);
    }

    public function index(Request $request)
    {
        $employeeLeave = $this->services->list($request->all());
        return $this->sendResponse($employeeLeave, $employeeLeave['message']);
    }

    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError($data['message']);
        }
        return $this->sendResponse($data, $data['message']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'onlyStatus' => 'required',
            'start_date' => 'required_if:onlyStatus,0',
            'end_date'   => 'required_if:onlyStatus,0',
            'taken'      => 'required_if:onlyStatus,0',
            'status'     => 'required_if:onlyStatus,1',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $data = $request->all();
        $isUpdated = $this->services->updatePaidLeave($data, $id);
        if (!$isUpdated['status']) {
            return $this->sendError($isUpdated['message']);
        }
        return $this->sendResponse(array('success' => 1), $isUpdated['message']);
    }

    public function destroy($id)
    {
        $isDeleted = $this->services->delete($id);
        if (!$isDeleted['status']) {
            return $this->sendError($isDeleted['message']);
        }
        return $this->sendResponse(array('success' => 1), $isDeleted['message']);
    }
}
