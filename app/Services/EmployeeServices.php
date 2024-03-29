<?php

namespace App\Services;

use App\Http\Resources\PaginationResource;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Repositories\Employee\EmployeeInterface;
use App\Repositories\UserVerification\UserVerificationInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeServices
{
    public $employee, $userVerification;

    public function __construct(EmployeeInterface $employee, UserVerificationInterface $userVerification)
    {
        $this->employee = $employee;
        $this->userVerification = $userVerification;
    }

    /**
     * Get list employee
     * If the show parameter is set, then get the paginated employees, otherwise get all employees
     * 
     * @param array $param
     */
    public function list($param = [])
    {
        if (isset($param['show']) && $param['show'] != null) {
            $data = $this->employee->getPaginateEmployee(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['departement']) ? $param['departement'] : null,
                isset($param['job_status']) ? $param['job_status'] : null,
                isset($param['status']) ? $param['status'] : null,
                $param['show'],
            );
            $employees = new PaginationResource($data);
        } else {
            $employees = $this->employee->getAllEmployee(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['departement']) ? $param['departement'] : null,
                isset($param['job_status']) ? $param['job_status'] : null,
                isset($param['status']) ? $param['status'] : null,
            );
        }
        return [
            'status'    => true,
            'message'   => 'Employee List Fetched Successfully',
            'data'      => $employees,
        ];
    }

    /**
     * It creates an employee, assigns the employee to a department, creates a bank account, creates a
     * salary, creates an attendance cut, and sends an email
     * 
     * @param array array
     * 
     * @return array array with a status and message.
     */
    public function create($data = [])
    {
        DB::beginTransaction();
        try {
            $employeeData = $data;
            $password = randomPassword();
            if (isset($data['profile_picture']) && $data['profile_picture']) {
                $employeeData['profile_picture'] = $data['profile_picture'];
            }
            $employeeData['password'] = Hash::make($password);
            $employeeData['job_position'] = ucfirst($data['job_position']);
            
            $employee = $this->employee->createEmployee($employeeData);

            $this->employee->assignEmployeeToDepartment($employee, $data['department']);
            $this->employee->createBankAccount([
                'source_type'           => User::class,
                'source_id'             => $employee->id,
                'bank_name'             => $data['bank_name'],
                'account_number'        => $data['account_number'],
                'account_holder_name'   => $data['account_holder_name']
            ]);

            if (isset($data['salary'])) {
                foreach ($data['salary'] as $salary) {
                    // THIS IS FOR OVERTIME
                    $salarySetting = [
                        'type'      => OVERTIME,
                        'setting'   => [
                            'paid_at'   => 'hour',
                        ],
                    ];
                    $salaryComponentType = DB::table('salary_component')->find($salary['salary_component_id']);
                    if (!$salaryComponentType) {
                        DB::rollBack();
                        return [
                            'status'    => false,
                            'message'   => 'Salary Not Found', 
                        ];
                    }
                    $this->employee->salaryInput($employee, [
                        'branch_id'           => branchSelected('sanctum:manager')->id,
                        'type'                => $salaryComponentType->type,
                        'salary_component_id' => $salary['salary_component_id'],
                        'amount'              => $salaryComponentType->type == SALARY_CUTS ? -$salary['amount'] : $salary['amount'], 
                        'setting'             => $salaryComponentType->name == OVERTIME || $salaryComponentType->name == 'Overtime' ? json_encode($salarySetting) : null,             
                    ]);
                }
            }

            if (isset($data['cuts']) && !is_null($data['cuts'])) {
                foreach ($data['cuts'] as $cuts) {
                    if ($cuts['total'] !== '') {
                        $this->employee->attendanceCutInput($employee, [
                            'cut_type'  => $cuts['cut_type'],
                            'total'     => $cuts['total'],
                            'amount'    => $cuts['amount'],
                        ]);
                    }
                }
            }

            $mailKey = generate_email_verification_key();
            $dataEmail = [
                'name'      => $data['firstname'].' '. isset($data['lastname']) ? $data['lastname'] : '',
                'email'     => $data['email'],
                'password'  => $password,
                'key'       => $mailKey,
            ];

            dispatch(new SendEmailJob($dataEmail, USER_EMPLOYEE_TYPE));

            DB::commit();
            return [
                'status'    => true,
                'message'   => 'Employee Created Successfully', 
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'message'   => $err->getMessage(), 
            ];
        }
    }

    /**
     * It updates the employee data in the database
     * 
     * @param array data The data to be updated
     * @param integer id The id of the employee you want to update
     * 
     * @return array array with two keys, status and message.
     */
    public function update($data = [], $id)
    {
        DB::beginTransaction();
        try {
            $this->employee->updateEmployee($data, $id);
            $assignedEmployee = [];
            
            if (isset($data['status'])) {
                $assignedEmployee['status'] = $data['status'];
            }

            if (isset($data['department'])) {
                $assignedEmployee['division_id'] = $data['department'];
            }
            $this->employee->updateBranchEmployee($assignedEmployee, $id);
            DB::commit();
            return [
                'status'    => true,
                'message'   => 'Employee Updated Successfully', 
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => true,
                'message'   => 'Internal Server Error', 
            ];
        }
    }

    /**
     * It deletes an employee from the database
     * 
     * @param array $data This is the data that you want to pass to the model.
     * 
     * @return array array with a status and message.
     */
    public function delete($data = [])
    {
        DB::beginTransaction();
        try {
            $this->employee->deleteEmployee($data);
            DB::commit();
            return [
                'status'    => true,
                'message'   => 'Employee Deleted Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'message'   => $err->getMessage(),
            ];
        }
    }

    /**
     * Detail data of the employee by ID
     * 
     * @param integer $id - ID of employee
     * @param string $param - Detail param type
     * @param array $request - Request data for filtering
     * 
     * @return object
     */
    public function detail($id, $param, $request = [])
    {
        $data = $this->employee->detailEmployee($id, $param, $request);
        if (is_null($data)) {
            return [
                'status'    => false,
                'data'      => [],
                'message'   => 'Detail Type Param is Required',
            ];
        }

        return [
            'status'    => true,
            'data'      => $data,
            'message'   => 'Data Fetched Successfully',
        ];

    }

    /**
     * Update data employee finance
     * @param array $data - Request data
     * @param string $type - type of finance (payment_date, bank, salary, attendance_cut)
     * @param int $employeeId - Id Of employee
     * @return object
     */
    public function updateFinanceEmployee(array $data, $type, $employeeId)
    {
        DB::beginTransaction();
        try {
            switch ($type) {
                case 'payment_date':
                    $this->employee->updateFinanceEmployee($data, 'payment_date', $employeeId);
                    break;
                case 'bank':
                    $this->employee->updateFinanceEmployee($data, 'bank', $employeeId);
                    break;
                case 'salary_income':
                    $this->employee->updateFinanceEmployee($data['data'], 'salary_income', $employeeId);
                    break;
                case 'salary_cuts':
                    $this->employee->updateFinanceEmployee($data['data'], 'salary_cuts', $employeeId);
                    break;
                case 'attendance_cut':
                    $this->employee->updateFinanceEmployee($data['data'], 'attendance_cut', $employeeId);
                    break;
                default:
                    return [
                        'status'  => false,
                        'message' => 'Please provide a type',
                    ];
            }
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Data Employee Finance Updated Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err);
            return [
                'status'  => false,
                'message' => 'Internal Server Error',
            ];
        }
    }
}
