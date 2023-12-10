<?php

namespace App\Services;

use App\Repositories\Payroll\PayrollInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollServices
{
    protected $payroll;

    public function __construct(PayrollInterface $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * LIST PAYROLL SERVICES
     * @param array $param - request param
     * @return object
     */
    public function list($param = [])
    {
        $data = $this->payroll->listPayroll(
            isset($param['keyword']) ? $param['keyword'] : null,
            isset($param['date']) ? $param['date'] : null,
            isset($param['departement']) ? $param['departement'] : null,
        );

        $payrollStatus = PayrollStatusService::getPayrollStatus(branchSelected('sanctum:manager')->id, $param['date']['month'], $param['date']['years']);

        return [
            'status'  => true,
            'message' => 'List Payroll Fetched Successully',
            'data'    => [
                'payslip_status' => $payrollStatus,
                'list'           => $data,
            ],
        ];
    }

    /**
     * DETAIL PAYROLL SERVICES
     * @param integer $id - EMPLOYEE ID
     * @param array $param
     * @return object
     */
    public function show($id, $param = [])
    {
        $detail = $this->payroll->detailPayroll($id, $param);
        if (!$detail) {
            return [
                'status'  => false,
                'message' => 'Payroll Not Found',
                'data'    => null, 
            ];
        }

        return [
            'status'  => true,
            'message' => 'Payroll Fetched Successfully',
            'data'    => $detail,
        ];
    }

    /**
     * STORE NEW PAYROLL SERVICES
     * @param array $data - data to insert
     * @return object
     * @throws \Exception
     */
    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $this->payroll->storePayroll($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Payroll Successfully Created',
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
     * STORE NEW OR UPDATE PAYROLL SERVICES
     * @param array $param - param to update
     * @param array $data - data to insert
     * @return object
     * @throws \Exception
     */
    public function storeOrUpdate(array $param, array $data)
    {
        DB::beginTransaction();
        try {
            $this->payroll->storeOrUpdate($param, $data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Payroll Successfully Created or Updated',
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
     * UPDATE EXISTING PAYROLL SERVICES
     * @param array $data - data to update
     * @param ingeter $id - id payroll
     * @return object
     * @throws \Exception
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $this->payroll->updatePayroll($data, $id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Payroll Successfully Updated',
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