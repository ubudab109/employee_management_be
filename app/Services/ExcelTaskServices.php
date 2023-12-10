<?php

namespace App\Services;

use App\Jobs\PayslipExportJob;
use App\Repositories\ExcelTask\ExcelTaskInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelTaskServices
{
    public $excelTask;

    public function __construct(ExcelTaskInterface $excelTask)
    {
        $this->excelTask = $excelTask;
    }

    /**
     * SERVICE GET LIST EXCEL TASK
     * @param array $param - Param Filter
     * @return array
     */
    public function list($param = [])
    {
        $data = $this->excelTask->listExcelTask(
            isset($param['model']) && $param['model'] != null ? $param['model'] : null,
            isset($param['type']) && $param['type'] != null ? $param['type'] : null,
            isset($param['manager_id']) && $param['manager_id'] != null ? $param['manager_id'] : null,
            isset($param['status']) && $param['status'] != null ? $param['status'] : null,
        );

        return [
            'status'  => true,
            'message' => 'Data Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * GET DETAIL EXCEL TASK
     * @param int $taskId
     * @return object
     */
    public function detail($taskId)
    {
        $data = $this->excelTask->detailExcelTask($taskId);

        if (!$data) {
            return [
                'status'  => false,
                'message' => 'Data Not Found',
                'data'    => null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Data Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * STORE DATA EXCEL TASK
     * @param $data - Data to create
     * @return object
     */
    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $created = $this->excelTask->createExcelTask($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Data Created Successfully',
                'data'    => $created,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error',
                'data'    => null,
            ];
        }
    }
}