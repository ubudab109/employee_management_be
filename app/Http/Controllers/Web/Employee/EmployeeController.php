<?php

namespace App\Http\Controllers\Web\Employee;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddEmployeeRequest;
use App\Services\EmployeeServices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'phone_number'                      => '',
            'pob'                               => '',
            'dob'                               => '',
            'religion'                          => '',
            'gender'                            => '',
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
            'job_position'                      => 'required',
            'is_address_same'                   => '',
            'join_date'                         => 'required',
            'end_date'                          => 'required_unless:job_status,0',
            'salary'                            => 'array',
            'salary.*.amount'                   => 'required',
            'cuts'                              => 'array',
            'bank_name'                         => 'required',
            'account_holder_name'               => 'required',
            'account_number'                    => 'required',
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'firstname'                         => 'required',
            'lastname'                          => '',
            'email'                             => ['required', 'email', Rule::unique('users','email')->ignore($id)],
            'mobile_phone'                      => 'required',
            'phone_number'                      => '',
            'pob'                               => '',
            'dob'                               => '',
            'religion'                          => '',
            'gender'                            => '',
            'marital_status'                    => '',
            'blood_type'                        => '',
            'identity_type'                     => 'required',
            'identity_number'                   => 'required',
            'is_address_same'                   => 'required',
            'postal_code'                       => '',
            'citizent_address'                  => '',
            'resident_address'                  => '',
            'nip'                               => ['required', Rule::unique('users','nip')->ignore($id)],
            'job_level'                         => 'required',
            'job_status'                        => 'required',
            'department'                        => 'required',
            'job_position'                      => 'required',
            'join_date'                         => 'required',
            'end_date'                          => 'required_unless:job_status,0',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors',$validator->errors());
        }

        $input = $request->all();
        $updated = $this->services->update($input, $id);
        if (!$updated['status']) {
            return $this->sendError($updated['message']);
        }

        return $this->sendResponse(array('success' => 1), $updated['message']);
    }

    public function updateFinance(Request $request, $id)
    {
        $type = $request->get('type');
        if (empty($type)) {
            return $this->sendBadRequest('Failed', 'Please provide type before update');
        }
        switch($type) {
            case 'payment_date':
                $validation = [
                    'payment_date' => 'required',
                ];
                break;
            case 'bank':
                $validation = [
                    'bank_name'           => 'required',
                    'account_number'      => 'required',
                    'account_holder_name' => 'required',
                ];
                break;
            case 'salary_income':
                $validation = [
                    'data'                       => 'required|array',
                    'data.*.salary_component_id' => 'required',
                    'data.*.amount'              => 'required',
                ];
                break;
            case 'salary_cuts':
                $validation = [
                    'data'                       => 'required|array',
                    'data.*.salary_component_id' => 'required',
                    'data.*.amount'              => 'required',
                ];
                break;
            case 'attendance_cut':
                $validation = [
                    'data'            => 'required|array',
                    'data.*.cut_type' => 'required',
                    'data.*.total'    => 'required',
                    'data.*.amount'   => 'required',
                ];
                break;
            default: 
              $validation = [];
          }

          $validator = Validator::make($request->all(), $validation);

          if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
          }

          $input = $request->all();
          $isUpdated = $this->services->updateFinanceEmployee($input, $type, $id);
          if (!$isUpdated['status']) {
            return $this->sendError(array('success' => 0), $isUpdated['message']);
          }

          return $this->sendResponse(array('success' => 1), $isUpdated['message']);
    }

    public function show(Request $request, $id)
    {
        $dataRequest = $request->except(['type']);
        return $this->services->detail($id, $request->type, $dataRequest);
    }

    public function destroy(Request $request)
    {
        $delete = $this->services->delete(json_decode($request->data));

        if ($delete['status']) {
            return $this->sendResponse(array('success' => 1), $delete['message']);
        } else {
            return $this->sendError(array('success' => 0), 'Internal Server Error');
        }
    }

}
