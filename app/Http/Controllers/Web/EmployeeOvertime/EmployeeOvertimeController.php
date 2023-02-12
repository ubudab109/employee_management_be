<?php

namespace App\Http\Controllers\Web\EmployeeOvertime;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Services\EmployeeOvertimeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeOvertimeController extends BaseController
{
    public $services;

    public function __construct(EmployeeOvertimeServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:employee-overtime-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:employee-overtime-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:employee-overtime-delete',['only' => 'delete']);
        $this->middleware('userpermissionmanager:employee-overtime-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:employee-overtime-assign',['only' => 'assignPayroll']);
    }

    public function index(Request $request)
    {
        $employeeOvertime = $this->services->list($request->all());
        return $this->sendResponse($employeeOvertime, $employeeOvertime['message']);
    }

    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError($data['message']);
        }
        return $this->sendResponse($data['data'], $data['message']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'onlyStatus' => 'required',
            'date'       => 'required_if:onlyStatus,0',
            'in'         => 'required_if:onlyStatus,0',
            'out'        => 'required_if:onlyStatus,0',
            'status'     => 'required_if:onlyStatus,1',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        
        $data = $request->all();

        $isUpdated = $this->services->updateOvertime($data, $id);
        if (!$isUpdated['status']) {
            return $this->sendError($isUpdated['message']);
        }
        
        return $this->sendResponse(array('success' => 1) ,$isUpdated['message']);
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