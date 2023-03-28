<?php

namespace App\Http\Controllers\Web\Payslip;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePayrollJob;
use App\Jobs\SendPayslipEmailJob;
use App\Models\PayrollGenerateProcess;
use App\Services\PayrollServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayslipController extends BaseController
{
    public $services;

    /**
     * A constructor function that is called when the class is instantiated.
     * 
     * @param PayrollServices services The service class that will be used to perform the actions.
     */
    public function __construct(PayrollServices $services)
    {
        $this->services = $services;
        if (config('app.env') != 'development') {
            $this->middleware('userpermissionmanager:payroll-management-list',['only' => 'index']);
            $this->middleware('userpermissionmanager:payslip-detail',['only' => 'show']);
            $this->middleware('userpermissionmanager:payslip-edit',['only' => 'update']);
            $this->middleware('userpermissionmanager:payslip-generate',['only' => 'generate']);
            $this->middleware('userpermissionmanager:payslip-send',['only' => 'send']);
        }
    }

    /**
     * LIST PAYSLIP EMPLOYEE IN SPESIFIC MONTH AND YEARS
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (empty($request->get('date'))) {
            return $this->sendBadRequest('Param date is required');
        }

        if (empty($request->get('date')['month'])) {
            return $this->sendBadRequest('Param month is required');
        }

        if (empty($request->get('date')['years'])) {
            return $this->sendBadRequest('Param years is required');
        }
        $paySlip = $this->services->list($request->all());
        return $this->sendResponse($paySlip, $paySlip['message']);
    }

    /**
     * SHOW DETAIL PAYSLIP EMPLOYEE
     * @param Request $request 
     * @param integer $employeeId - employeeId OF EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function show(Request $request, $employeeId)
    {
        $paySlip = $this->services->show($employeeId, $request->all());
        if (!$paySlip['status']) {
            return $this->sendError($paySlip['message']);
        }
        return $this->sendResponse($paySlip, $paySlip['message']);
    }

    /**
     * UPDATE DATA PAYSLIP EMPLOYEE BY ID PAYSLIP EMPLOYEE
     * @param Request $request
     * @param integer $id - ID OF PAYSLIP EMPLOYEE
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount'  => 'required',
            'type'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        if ($request->input('type') == 'income') {
            $amount = $request->input('amount');
        } else if ($request->input('type') == 'cut') {
            $amount = -$request->input('amount');
        } else {
            return $this->sendBadRequest('Failed', 'Type is invalid');
        }

        $input = [
            'amount' => $amount
        ];
        
        $isUpdated = $this->services->update($input, $id);
        if (!$isUpdated['status']) {
            return $this->sendError(array('success' => 0), $isUpdated['message']);
        }
        return $this->sendResponse(array('success' => 1), $isUpdated['message']);
    }

    /**
     * LIST GENERATED PAYSLIP IN SPESIFIC MONTH AND YEAR
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function listPayslipGenerateProcess(Request $request)
    {
        $data = DB::table('payroll_generate_process')
        ->where('branch_id', branchSelected('sanctum:manager')->id)
        ->when($request->has('month') && $request->month != null, function ($query) use ($request) {
            $query->where('month', $request->month);
        })
        ->when($request->has('years') && $request->years != null, function ($query) use ($request) {
            $query->where('years', $request->years);
        })
        ->get();
        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GENERATE PAYSLIP WITH SPESIFIC MONTH AND YEAR
     * WILL DISPACTHING A GENERATEPAYSLIP JOB
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month'         => 'required',
            'years'         => 'required',
            'type'          => 'required',
            'employee_id'   => 'required_if:type,selected'
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $checkPayrollGenerate = PayrollGenerateProcess::where('month', $request->get('month'))
        ->where('years', $request->get('years'))
        ->where('branch_id', branchSelected('sanctum:manager')->id)
        ->whereIn('status', [GENERATING, GENERATED]);

        if ($checkPayrollGenerate->exists() && $request->get('type') == 'all') {
            return $this->sendBadRequest('Failed', ['message' => 'Payslip already generated on this month and years']);
        }

        $dataParam = [
            'month'         => $request->get('month'),
            'years'         => $request->get('years'),
            'type'          => $request->get('type'),
            'employee_id'   => json_decode($request->get('employee_id')),
        ];

        if ($request->get('type') == 'all') {
            $payrollProcess = PayrollGenerateProcess::create([
                'month'     => $request->get('month'),
                'years'     => $request->get('years'),
                'branch_id' => branchSelected('sanctum:manager')->id,
            ]);
        } else if ($request->get('type') == 'selected') {
            if ($checkPayrollGenerate->exists()) {
                $payrollProcess = $checkPayrollGenerate->first();
            } else {
                $payrollProcess = PayrollGenerateProcess::create([
                    'month'     => $request->get('month'),
                    'years'     => $request->get('years'),
                    'branch_id' => branchSelected('sanctum:manager')->id,
                ]);
            }
        }

        GeneratePayrollJob::dispatch($this->services, $payrollProcess->fresh(), $dataParam);

        return $this->sendResponse(array('success' => 1), 'Payslip Successfully Generating in Queue');
    }

    /**
     * RE-GENERATE A PAYSLIP
     * USE THIS IF GENERATING PAYSLIP IS FAILED
     * WILL DISPATCHING A GENERATEPAYSLIP JOB
     * @param integer $id - ID OF GENERATED PAYSLIP
     * @return Illuminate\Http\Response
     */
    public function retryGenerate($id)
    {
        $payrollProcess = PayrollGenerateProcess::find($id);
        $dataParam = [
            'month' => $payrollProcess->month,
            'years' => $payrollProcess->years,
            'type'  => 'all',
        ];
        GeneratePayrollJob::dispatch($this->services, $payrollProcess->fresh(), $dataParam);
        return $this->sendResponse(array('success' => 1), 'Payslip Successfully Generating in Queue');
    }

    public function sendPayslip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month'      => 'required',
            'year'       => 'required',
            'type'       => 'required',
            'employeeId' => 'required_if:type,selected',
        ]);
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        $month = $request->input('month');
        $year = $request->input('year');
        $type = $request->input('type');
        $employeeId = $request->input('employeeId');
        SendPayslipEmailJob::dispatch($month, $year, branchSelected('sanctum:manager')->id, $type, json_decode($employeeId, true));
        return $this->sendResponse(array('success' => 1), 'Payslip successfully sending to employee');
    }
}
