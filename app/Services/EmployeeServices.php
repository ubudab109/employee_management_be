<?php

namespace App\Services;

use App\Http\Resources\PaginationResource;
use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Repositories\Employee\EmployeeInterface;
use App\Repositories\UserVerification\UserVerificationInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                    $this->employee->salaryInput($employee, [
                        'type'          => $salary['type'],
                        'name'          => $salary['name'],
                        'amount'        => $salary['type'] == SALARY_CUTS ? -$salary['amount'] : $salary['amount'],                    
                    ]);
                }
            }

            if (isset($data['cuts']) && !is_null($data['cuts'])) {
                foreach ($data['cuts'] as $cuts) {
                    $this->employee->attendanceCutInput($employee, [
                        'cut_type'  => $cuts['cut_type'],
                        'total'     => $cuts['total'],
                        'amount'    => $cuts['amount'],
                    ]);
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
            DB::commit();
            $this->employee->deleteEmployee($data);
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
}
