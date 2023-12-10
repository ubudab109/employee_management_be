<?php

namespace App\Services;

use App\Models\EmployeeLeave;
use App\Repositories\EmployeeLeave\EmployeeLeaveInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\NotificationTrait;

class EmployeeLeaveServices
{
    public $employeeLeave;

    public function __construct(EmployeeLeaveInterface $employeeLeave)
    {
        $this->employeeLeave = $employeeLeave;
    }

    /**
     * LIST EMPLOYEE LEAVE. CAN BE PAGINATE OR NOT
     * @param array $param
     * @return object
     */
    public function list($param = [])
    {
        if (isset($param['show']) && !is_null($param['show'])) {
            $data = $this->employeeLeave->listEmployeePaidLeavePaginate(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['department']) ? $param['department'] : null,
                isset($param['date']) ? $param['date'] : null,
                isset($param['status']) ? $param['status'] : null,
                isset($param['type']) ? $param['type'] : null,
                $param['show'],
                isset($param['employee_id']) ? $param['employee_id'] : null,
                isset($param['branch_id']) ? $param['branch_id'] : null,
            );
        } else {
            $data = $this->employeeLeave->listEmployeePaidLeave(
                isset($param['keyword']) ? $param['keyword'] : null,
                isset($param['department']) ? $param['department'] : null,
                isset($param['date']) ? $param['date'] : null,
                isset($param['status']) ? $param['status'] : null,
                isset($param['type']) ? $param['type'] : null,
                isset($param['employee_id']) ? $param['employee_id'] : null,
                isset($param['branch_id']) ? $param['branch_id'] : null,
            );
        }

        return [
            'status'  => true,
            'message' => 'Employee Paid Leave Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * It returns an array with a status, message, and data key.
     * 
     * @param integer id The id of the employee paid leave
     * 
     * @return Collection An array with three keys: status, message, and data.
     */
    public function detail($id)
    {
        $data = $this->employeeLeave->detailEmployeePaidLeave($id);

        if (is_null($data)) {
            return [
                'status'  => false,
                'message' => 'Employee Paid Leave Not Found',
                'data'    => null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Employee Paid Leave Fetched Successfully',
            'data'    => $data
        ];
    }

    /**
     * It creates a paid leave for an employee
     * 
     * @param array data 
     * @return array An array with two keys, status and message.
     */
    public function createPaidLeave(array $data)
    {
        DB::beginTransaction();
        try {
            $this->employeeLeave->createEmployeePaidLeave($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Paid Leave Created Successfully',
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
     * It updates the employee paid leave
     * 
     * @param array data array of data to be updated
     * @param integer id The id of the record to be updated 
     * @return array An array with two keys: status and message.
     */
    public function updatePaidLeave(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $this->employeeLeave->updateEmployeePaidLeave($data, $id);
            if (Auth::guard('sanctum:manager')->check()) {
                $paidLeaveData = $this->employeeLeave->detailEmployeePaidLeave($id);
                $date = date("j F Y", strtotime($paidLeaveData->start_date));
                NotificationTrait::dispatchNotificationToEmployee($paidLeaveData->employee, 'Paid Leave Status', 'Your Paid Leave request at '.$date.' had been '.getLeaveStatusName($data['status']), EmployeeLeave::class, $id, PAID_LEAVE);
            }
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Paid Leave Updated Successfully',
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
     * It deletes an employee paid leave
     * 
     * @param integer id The id of the employee paid leave
     * @return array An array with two keys: status and message.
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $this->employeeLeave->deleteEmployeePaidLeave($id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Paid Leave Deleted Successfully',
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
}