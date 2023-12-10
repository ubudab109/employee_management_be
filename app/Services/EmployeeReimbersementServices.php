<?php

namespace App\Services;

use App\Models\EmployeeReimburshment;
use App\Repositories\EmployeeReimbersement\EmployeeReimbursementInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\NotificationTrait;
use Illuminate\Support\Facades\Auth;

class EmployeeReimbersementServices
{
    protected $reimbersement;

    public function __construct(EmployeeReimbursementInterface $reimbersement)
    {
        $this->reimbersement = $reimbersement;
    }

    /**
     * LIST DATA REIMBERSEMENT
     * @param array $param - Request Data
     * @return object
     */
    public function list($param = []) 
    {
        $data = $this->reimbersement->listReimbersement(
            isset($param['keyword']) && $param['keyword'] != '' ? $param['keyword'] : null,
            isset($param['date']) && $param['date'] != '' ? $param['date'] : null,
            isset($param['employee_id']) && $param['employee_id'] != null ? $param['employee_id'] : null,
            isset($param['claim_type_id']) && $param['claim_type_id'] != '' ? $param['claim_type_id'] : null,
            isset($param['status']) && $param['status'] != null ? $param['status'] : 'All',
        );

        return [
            'status'  => true,
            'message' => 'Employee Reimbursement List Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * DETAIL REIMBERSEMENT BY ID
     * @param integer $id
     * @return object
     */
    public function detail($id) 
    {
        $data = $this->reimbersement->detailReimbersement($id);
        
        if (is_null($data)) {
            return [
                'status'  => false,
                'message' => 'Employee Reimbersement Not Found',
                'data'    => null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Employee Reimbersement Detail Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * CREATE REIMBERSEMENT
     * @param array $data
     * @return object
     */
    public function createReimbersement(array $data) 
    {
        DB::beginTransaction();
        try {
            $this->reimbersement->createReimbersement($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Reimbersement Created Successfully',
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
     * UPDATE REIMBERSEMENT BY ID
     * @param array $data
     * @param integer $id
     * @return object
     */
    public function updateReimbersement(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $this->reimbersement->updateReimbersement($data, $id);
            if (Auth::guard('sanctum:manager')->check()) {
                $reimbersementData = $this->reimbersement->detailReimbersement($id);
                $date = date("j F Y", strtotime($reimbersementData->date));
                NotificationTrait::dispatchNotificationToEmployee($reimbersementData->employee, 'Reimbursement Status', 'Your Reimbursement request at '.$date.' had been '.getGlobalStatusEnum($data['status']), EmployeeReimburshment::class, $id, null);
            }
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Employee Reimbersement Updated Successfully',
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