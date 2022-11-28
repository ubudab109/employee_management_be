<?php

namespace App\Http\Controllers\Web\Employee;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddEmployeeRequest;
use App\Services\EmployeeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends BaseController
{
    public $services;

    public function __construct(EmployeeServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:employee-management-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:employee-management-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:employee-management-create',['only' => 'store']);
        $this->middleware('userpermissionmanager:employee-management-delete',['only' => 'delete']);
        $this->middleware('userpermissionmanager:employee-management-update',['only' => 'update']);
    }

    public function index(Request $request)
    {
        $employees = $this->services->list($request->all());
        return $this->sendResponse($employees, $employees['message']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'                         => 'required',
            'lastname'                          => '',
            'email'                             => 'required|email|unique:users,email',
            'mobile_phone'                      => 'required',
            'phone_number'                      => 'unique:users,phone_number',
            'pob'                               => '',
            'dob'                               => '',
            'gender'                            => 'required',
            'marital_status'                    => '',
            'blood_type'                        => '',
            'identity_type'                     => 'required',
            'identity_number'                   => 'required',
            'ientity_expired'                   => '',
            'postal_code'                       => 'required',
            'citizent_address'                  => 'required',
            'resident_address'                  => 'required',
            'nip'                               => 'required|unique:users,nip',
            'job_level'                         => 'required',
            'job_status'                        => 'required',
            'department'                        => 'required',
            'join_date'                         => 'required',
            'job_position'                      => 'required',
            'end_date'                          => 'required_if:job_status,!=,0',
            'salary'                            => 'array',
            'cuts'                              => 'array',
            'bank_name'                         => 'required',
            'account_holder_name'               => 'required',
            'account_number'                    => 'required',
            'division_id'                       => 'required',
            'payment_date'                      => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors',$validator->errors());
        }

        $input = $request->all();
        $input['salary_settings']['currency'] = DEFAULT_CURRENCY;

        $invEmployee = $this->services->create($input);

        if (!$invEmployee['status']) {
            return $this->sendError($invEmployee['message']);
        }

        return $this->sendResponse(array('success' => 1), 'Employee Invited Successfully');
    }


    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data'          => 'array|required',
            'data.*.id'     => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $delete = $this->services->delete($request->data);

        if ($delete['status']) {
            return $this->sendResponse(array('success' => 1), $delete['message']);
        } else {
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
    }

}
