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

    /**
     * The above function is a constructor function that is used to call the middleware function
     * 
     * @param EmployeeServices services The service class that will be used to perform the CRUD
     * operations.
     */
    public function __construct(EmployeeServices $services)
    {
        $this->services = $services;
        if (config('app.env') != 'development') { 
            $this->middleware('userpermissionmanager:employee-management-list',['only' => 'index']);
            $this->middleware('userpermissionmanager:employee-management-detail',['only' => 'detail']);
            $this->middleware('userpermissionmanager:employee-management-create',['only' => 'store']);
            $this->middleware('userpermissionmanager:employee-management-delete',['only' => 'delete']);
            $this->middleware('userpermissionmanager:employee-management-update',['only' => 'update']);
        }
    }

    /**
     * LIST EMPLOYEE
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $employees = $this->services->list($request->all());
        return $this->sendResponse($employees, $employees['message']);
    }

    /**
     * STORE NEW EMPLOYEE
     * @param Request $request
     * @return Illuminate\Http\Response
     */
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

    /**
     * UPDATE GENERAL DATA EMPLOYEE 
     * @param Request $request
     * @param integer $id - ID of Employee
     * @return Illuminate\Http\Response
     */
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

    /**
     * UPDATE DATA FINANCE EMPLOYEE
     * @param Request $request
     * @param integer $id -ID of Employee
     * @return Illuminate\Http\Response
     */
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

    /**
     * DETAIL DATA EMPLOYEE WITH SEPESIC TYPE
     * TYPE: GENERAL, ATTENDANCE, TIME MANAGEMENT, FINANCE, WARNING LETTER
     * @param Request $request
     * @param integer $id - ID OF EMPLOYEE
     * @return Illuminate\Http\Response 
     */
    public function show(Request $request, $id)
    {
        $dataRequest = $request->except(['type']);
        return $this->services->detail($id, $request->type, $dataRequest);
    }

    /**
     * DELETE SELECTED EMPLOYEE BY ID
     * @param Request $request
     * @return Illuminate\Http\Response
     */
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
