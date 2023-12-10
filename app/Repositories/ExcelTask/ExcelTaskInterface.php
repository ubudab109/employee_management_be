<?php

namespace App\Repositories\ExcelTask;

interface ExcelTaskInterface
{
    /**
     * GET LIST TASK EXCEL
     * @param string $model - Model of excel
     * @param string $type - Type of task excel (EXCEL_EXPORT or EXCEL_IMPORT)
     * @param int $managerId - Manager id
     * @param string $status - Status of excel task
     * @return Collection
     */
    public function listExcelTask($model, $type, $managerId, $status);

    /**
     * GET DETAIL EXCEL TASK
     * @param int $taskId - ID of task
     * @return object
     */
    public function detailExcelTask($taskId);

    /**
     * CREATE NEW EXCEL TASK
     * @param array $data
     * @return object
     */
    public function createExcelTask(array $data);
}