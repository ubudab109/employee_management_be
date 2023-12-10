<?php

namespace App\Http\Controllers\Web\EmployeeReimbersement;

use App\Http\Controllers\BaseController;
use App\Jobs\ReimbursementExportJob;
use App\Models\EmployeeReimburshment;
use App\Services\EmployeeReimbersementServices;
use App\Services\ExcelTaskServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeReimbersementController extends BaseController
{
    public $services, $excelServices;

    /**
     * A constructor function. It is used to initialize the services variable.
     * 
     * @param EmployeeReimbersementServices services The service class that will be used to get the
     * data from the database.
     */
    public function __construct(EmployeeReimbersementServices $services, ExcelTaskServices $excelServices)
    {
        $this->services = $services;
        $this->excelServices = $excelServices;
        if (config('app.env') != 'development') {
            $this->middleware('userpermissionmanager:employee-reimbursement-list', ['only' => 'index']);
            $this->middleware('userpermissionmanager:employee-reimbursement-detail', ['only' => 'detail']);
            $this->middleware('userpermissionmanager:employee-reimbursement-update', ['only' => 'update']);
        }
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

    /**
     * EXPORT REIMBURSEMENT DATA
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'export_type'      => 'required',
            'type'             => 'required_if:export_type,all',
            'date'             => 'required_if:export_type,all',
            'reimbursement_id' => 'required_if:type,selected|array',
            'month'            => 'required_if:export_type,employee',
            'year'             => 'required_if:export_type,employee',
            'employee_id'      => 'required_if:export_type,employee',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $data = [
            'branch_id'   => branchSelected('sanctum:manager')->id,
            'manager_id'  => Auth::guard('sanctum:manager')->user()->id,
            'source_type' => EmployeeReimburshment::class,
            'type'        => EXCEL_EXPORT,
            'date'        => $request->date,
            'month'       => null,
            'year'        => null,
            'employee_id' => null,
            'id'          => [],
        ];

        if ($request->export_type == 'all') {
            if ($request->type == 'selected') {
                $data['id'] = $request->reimbursement_id;
            } else {
                $data['id'] = [];
            }

            $type = 'all';
        } else if ($request->export_type == 'employee') {
            $type = 'employee';
            $data['employee_id'] = $request->employee_id;
            $data['month'] = $request->month;
            $data['year'] = $request->year;
        } else {
            return $this->sendBadRequest('Validator Errors', 'Invalid type export');
        }
        $excelTask = $this->excelServices->create($data);

        if (!$excelTask['status']) {
            return $this->sendError(array('success' => 1), $excelTask['message']);
        }

        ReimbursementExportJob::dispatch($excelTask['data']->fresh(), $type, $data['date'], $data['id'], $data['month'], $data['year'], $data['employee_id']);

        return $this->sendResponse(array('success' => 1), $excelTask['message']);
    }

    /**
     * RETRY FAILED TASK
     * @param int $taskId
     * @return Illuminate\Http\Response
     */
    public function retry($taskId)
    {
        $excelTask = $this->excelServices->detail($taskId);
        if (!$excelTask['status']) {
            return $this->sendError('Error', 'Data Not Found', 404);
        }
        $data = $excelTask['data'];
        $setting = json_decode($data->settings, true);
        Log::info($setting);
        ReimbursementExportJob::dispatch($data->fresh(), $setting['type'], $setting['date'], $setting['id'], $setting['month'], $setting['year'], $setting['employee_id']);
        return $this->sendResponse(array('success' => 1), 'Task retried successfully');
    }
}
