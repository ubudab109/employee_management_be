<?php

namespace App\Services;

use App\Repositories\CompanySchedule\CompanyScheduleInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyScheduleServices
{
    public $companySchedule;

    public function __construct(CompanyScheduleInterface $companySchedule)
    {
        $this->companySchedule = $companySchedule;
    }

    /**
     * GET DEFAULT SCHEDULE AND LIST HISTORY SCHEDULE
     * @return array 
     */
    public function list()
    {
        $defaultSchedule = $this->companySchedule->getDefaultSchedule(branchSelected('sanctum:manager')->id);
        $historySchedule = $this->companySchedule->listSchedule(branchSelected('sanctum:manager')->id); 
        return [
            'status'  => true,
            'message' => 'Data Fetched Successfully',
            'data'    => [
                'default_schedule' => $defaultSchedule,
                'history_schedule' => $historySchedule
            ],
        ];
    }

    /**
     * DETAIL SCHEDULE
     * @param integer $id - ID OF Schedule
     * @return array
     */
    public function detail($id)
    {
        $data = $this->companySchedule->detailSchedule($id);

        if (!$data) {
            return [
                'status'  => false,
                'message' => 'Data Not Found',
                'data'    => null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Data successfully fetched',
            'data'    => $data,
        ];
    }

    /**
     * UPDATE DATA SCHEDULE
     * @param array $data - data to update
     * @param integer $id - id schedule
     * @return array
     */
    public function updateSchedule(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $this->companySchedule->updateSchedule($data, $id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Data updated successfully',
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
     * UPDATE NEW DEFAULT SCHEDULE
     * @param integer $id - ID Schedule
     * @return array
     */
    public function changeDefaultSchedule($id)
    {
        $branchId = branchSelected('sanctum:manager')->id;
        $getDefaultData = $this->companySchedule->getDefaultSchedule($branchId);

        if (!$getDefaultData) {
            return [
                'status'  => false,
                'message' => 'Data Default Schedule Not Found',
            ];
        }

        try {
            /* UPDATE OLD DEFAULT SCHEDULE FIRST */
            $this->companySchedule->updateSchedule(['is_default' => 0], $getDefaultData->id);

            /* THEN UPDATE NEW DEFAULT SCHEDULE */
            $this->companySchedule->updateSchedule(['is_default' => 1], $id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Default schedule changed successfully',
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
     * CREATE A NEW SCHEDULE
     * @param $data - data to create
     * @return array
     */
    public function createSchedule(array $data)
    {
        DB::beginTransaction();
        try {
            $this->companySchedule->createSchedule($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Data created successfully',
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