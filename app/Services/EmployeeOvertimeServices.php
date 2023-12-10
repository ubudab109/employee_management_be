<?php

namespace App\Services;

use App\Jobs\InsertOvertimeToPayrollJob;
use App\Models\EmployeeOvertime;
use App\Repositories\EmployeeOvertime\EmployeeOvertimeInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\NotificationTrait;

class EmployeeOvertimeServices
{
    public $employeeOvertime;

    /**
     * A constructor function.
     * 
     * @param EmployeeOvertimeInterface employeeOvertime This is the interface that we created earlier.
     */
    public function __construct(EmployeeOvertimeInterface $employeeOvertime)
    {
        $this->employeeOvertime = $employeeOvertime;
    }

    /**
     * LIST EMPLOYEE OVERTIME. CAN BE PAGINATE OR NOT
     * @param array $data
     * @return object
     */
    public function list($param = [])
    {
        if (isset($param['show']) && !is_null($param['show'])) {
            $data = $this->employeeOvertime->listEmployeeOvertimePaginate(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['department']) ? $param['department'] : null,
                isset($param['date']) ? $param['date'] : null,
                isset($param['status']) && $param['status'] != '' ? $param['status'] : '',
                $param['show'],
                isset($param['employee_id']) ? $param['employee_id'] : null,
                isset($param['branch_id']) ? $param['branch_id'] : null,
            );
        } else {
            $data = $this->employeeOvertime->listEmployeeOvertime(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['department']) ? $param['department'] : null,
                isset($param['date']) ? $param['date'] : null,
                isset($param['status']) ? $param['status'] : null,
                isset($param['employee_id']) ? $param['employee_id'] : null,
                isset($param['branch_id']) ? $param['branch_id'] : null,
            );
        }

        return [
            'status'  => true,
            'message' => 'Employee Overtime List Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * DETAIL EMPLOYEE OVERTIME BY OVERTIME ID
     * @param integer $id
     * @return object
     */
    public function detail($id)
    {
        $data = $this->employeeOvertime->detailEmployeeOvertime($id);
        if (is_null($data)) {
            return [
                'status'  => false,
                'message' => 'Employee Overtime Not Found',
                'data'    => null, 
            ];
        }

        return [
            'status'  => true,
            'message' => 'Employee Overtime Detail Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * CREATE OVERTIME EMPLOYEE
     * @param array $data
     * @return object
     * @throws \Exception
     */
    public function createOvertime(array $data)
    {
        DB::beginTransaction();
        try {
            $this->employeeOvertime->createEmployeeOvertime($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Overtime Created Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error',
            ];
        }
    }

    /**
     * UPDATE OVERTIME EMPLOYEE BY ID OVERTIME
     * @param array $data
     * @param integer $id
     * @return object
     * @throws \Exception
     */
    public function updateOvertime(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $this->employeeOvertime->updateEmployeeOvertime($data, $id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Overtime Updated Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error' 
            ];
        }
    }

    /**
     * It updates the status of the overtime of the employee.
     * @param array $data
     * @param integer $id
     * @return object
     * @throws \Exception
     */
    public function updateOvertimeStatus(array $data)
    {
        DB::beginTransaction();
        try {
            foreach ($data['id'] as $id) {
                // CHECK IF CURRENT OVERTIME DATA HAD BEEN APPLIED OR REJECTED
                $overtimeData = $this->employeeOvertime->detailEmployeeOvertime($id);

                // WILL SKIP THE PROCESS
                if ($overtimeData->status == APPROVED || $overtimeData == REJECTED) {
                    continue;
                }

                /** PROCESS TO INPUT THE OVERTIME TO PAYROLL EMPLOYEE */
                $checkPayslip = DB::table('employee_payslip_status')->where('branch_id', $overtimeData->branch_id)
                ->where('month', date('j', strtotime($overtimeData->date)))
                ->where('years', date('Y', strtotime($overtimeData->date)))
                ->first();
                
                if ($checkPayslip && $checkPayslip->status !== SENDED) {
                    $payroll = DB::table('payroll')->where('employee_id', $overtimeData->employee_id)
                    ->where('month', date('j', strtotime($overtimeData->date)))
                    ->where('years', date('Y', strtotime($overtimeData->date)))
                    ->first();
                    $dataJob = [
                        'branch_id'     => $overtimeData->branch_id,
                        'employee_id'   => $overtimeData->employee_id,
                        'department_id' => $overtimeData->department_id,
                        'payroll_code'  => $payroll->payroll_code,
                        'employee'      => $overtimeData->employee,
                        'gross_salary'  => $overtimeData->employee->gross_salary,
                        'taken_hour'    => $overtimeData->taken_hour,
                        'month'         => $payroll->month,
                        'years'         => $payroll->years,
                        'generate_date' => $payroll->generate_date,
                        'status'        => $payroll->status,
                        'type'          => SALARY_INCOME,
                    ];
                    InsertOvertimeToPayrollJob::dispatch($dataJob);
                } else {
                    return [
                        'status'  => false,
                        'message' => 'Payslip in current month and year is not generated or had been send to employee email. Please make sure You had been generate the payslip before apply the overtime to employee payroll' 
                    ];
                }
                
                NotificationTrait::dispatchNotificationToEmployee($overtimeData->employee, 'Overtime Status', 'Update for Your Overtime request', EmployeeOvertime::class, $id);
                $this->employeeOvertime->updateEmployeeOvertime(['status' => $data['status']], $id);
            }
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Overtime Status Updated Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage().' '. $err->getLine());
            return [
                'status'  => false,
                'message' => 'Internal Server Error' 
            ];
        }
    }

    /**
     * DELETE EMPLOYEE OVERTIME BY OVERTIME ID
     * @param integer $id
     * @return object
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $this->employeeOvertime->deleteEmployeeOvertime($id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Data Deleted Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error'
            ];
        }
    }
}