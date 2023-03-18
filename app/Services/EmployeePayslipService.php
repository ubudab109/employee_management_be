<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeePayslipService
{
    /**
     * It will update the record if it exists, otherwise it will create a new record
     * 
     * @param integer $branchId - selected branch
     * @param integer $employeeId - Employee ID
     * @param integer $month 1-12
     * @param integer $years - YYYY
     * @param string $status - generated, sended
     * @return bool return value is a boolean value.
     * @throws \Exception
     */
    public static function updateOrStore($branchId, $employeeId, $month, $years, $status)
    {
        DB::beginTransaction();
        try {
            $param = [
                'branch_id'   => $branchId,
                'employee_id' => $employeeId,
                'month'       => $month,
                'years'       => $years,
            ];
            $data = [
                'status' => $status,
            ];
            DB::table('employee_payslip_status')
            ->updateOrInsert($param, array_merge($param, $data));
            DB::commit();
            return true;
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            throw new \Exception('Unable to update or create employee payslip status');
        }
    }
}