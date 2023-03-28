<?php

namespace App\Http\Controllers\Web\EmployeeReimbersement;

use App\Http\Controllers\BaseController;
use App\Services\EmployeeReimbersementServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeReimbersementController extends BaseController
{
    public $services;

    /**
     * A constructor function. It is used to initialize the services variable.
     * 
     * @param EmployeeReimbersementServices services The service class that will be used to get the
     * data from the database.
     */
    public function __construct(EmployeeReimbersementServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:employee-reimbursement-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:employee-reimbursement-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:employee-reimbursement-update',['only' => 'update']);
    }

    /**
     * LIST REIMBERSEMENT EMPLOYEE
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employeeReimbersement = $this->services->list($request->all());
        return $this->sendResponse($employeeReimbersement, $employeeReimbersement['message']);
    }

    /**
     * SHOW DETAIL REIMBERSEMENT EMPLOYEE
     * @param integer $id - ID OF REIMBERSEMENT EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError($data['message']);
        }
        return $this->sendResponse($data['data'], $data['message']);
    }

    /**
     * UPDATE REIMBERSEMENT DATA EMPLOYEE
     * @param Request $request
     * @param integer $id - ID OF REIMBERSEMENT EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'onlyStatus' => 'required',
            'date'       => 'required_if:onlyStatus,0',
            'claim_type' => 'required_if:onlyStatus,0',
            'amount'     => 'required_if:onlyStatus,0',
            'status'     => 'required_if:onlyStatus,1',
        ]);
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        $isUpdated = $this->services->updateReimbersement($request->all(), $id);
        if (!$isUpdated['status']) {
            return $this->sendError($isUpdated['message']);
        }
        return $this->sendResponse(array('success' => 1), $isUpdated['message']);
    }

}
