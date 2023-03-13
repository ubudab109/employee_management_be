<?php

namespace App\Http\Controllers\Web\EmployeePaidLeave;

use App\Http\Controllers\BaseController;
use App\Services\EmployeeLeaveServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeePaidLeaveController extends BaseController
{
    public $services;

    /**
     * A constructor function. It is called when the class is instantiated.
     * 
     * @param EmployeeLeaveServices services The service class that contains the business logic.
     */
    public function __construct(EmployeeLeaveServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:employee-leave-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:employee-leave-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:employee-leave-delete',['only' => 'delete']);
        $this->middleware('userpermissionmanager:employee-leave-update',['only' => 'update']);
    }

    /**
     * LIST DATA PAID LEAVE EMPLOYEE
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employeeLeave = $this->services->list($request->all());
        return $this->sendResponse($employeeLeave, $employeeLeave['message']);
    }

    /**
     * DETAIL DATA PAID LEAVE EMPLOYEE
     * @param integer $id - ID OF PAID LEAVE EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError($data['message']);
        }
        return $this->sendResponse($data, $data['message']);
    }

    /**
     * UPDATE DATA EMPLOYEE PAID LEAVE
     * @param Request $request
     * @param integer $id - ID OF PAID LEAVE EMPLOYEE
     * @return Illuminate\Http\Response
     */
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

    /**
     * DELETE OVERTIME EMPLOYEE
     * @param integer $id - ID OF OVERTIME EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $isDeleted = $this->services->delete($id);
        if (!$isDeleted['status']) {
            return $this->sendError($isDeleted['message']);
        }
        return $this->sendResponse(array('success' => 1), $isDeleted['message']);
    }
}
