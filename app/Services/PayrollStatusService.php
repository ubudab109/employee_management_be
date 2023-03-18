<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollStatusService
{

    /**
     * It returns the first row of the payroll_status table where the branch_id, month, and years
     * columns match the values passed to the function.
     * 
     * @param integer branchId - Branch Selected
     * @param integer month - 1 to 12
     * @param integer years - YYYY
     * @return object
     */
    public static function getPayrollStatus($branchId, $month, $years)
    {
        $data = DB::table('payroll_status')
        ->where('branch_id', $branchId)
        ->where('month', $month)
        ->where('years', $years)
        ->first();

        return $data;
    }

    /**
     * It will update the record if it exists, otherwise it will create a new record
     * 
     * @param integer $branchId the branch id
     * @param integer $month 1-12
     * @param integer $years 2019
     * @param string status 0 = generated, 1 = partially sended, 2 = sended
     */
    public static function updateOrStore($branchId, $month, $years, $status)
    {
        DB::beginTransaction();
        try {
            $param = [
                'branch_id' => $branchId,
                'month'     => $month,
                'years'     => $years,
            ];
            $data = [
                'status'    => $status,
            ];
            DB::table('payroll_status')
            ->updateOrInsert($param, array_merge($param, $data));
            DB::commit();
            return true;
        } catch (\Exception $err) {
            Log::info($err->getMessage());
            DB::rollBack();
            throw new \Exception('Unable to create or update Payroll Status');
        }
    }
}