<?php

namespace App\Repositories\CompanySchedule;

use App\Models\CompanySchedule;

class CompanyScheduleRepository implements CompanyScheduleInterface
{
    /**
     * @var ModelName
     */
    protected $model;

    public function __construct(CompanySchedule $model)
    {
        $this->model = $model;
    }

    /**
     * LIST HISTORY SCHEDULE COMPANY BRANCH
     * @param integer $branchId - Selected Branch
     * @return array
     */
    public function listSchedule($branchId)
    {
        return $this->model->where('branch_id', $branchId)->get();
    }

    /**
     * GET DEFAULT SCHEDULE COMPANY BRANCH
     * @param $branchId - Selected Branch
     * @return object
     */
    public function getDefaultSchedule($branchId)
    {
        return $this->model->where('branch_id', $branchId)->where('is_default', true)->first();
    }

    /**
     * DETAIL SCHEDULE
     * @param integer $id - ID Schedule
     * @return object
     */
    public function detailSchedule($id)
    {
        return $this->model->find($id);
    }

    /**
     * UPDATE SCHEDULE
     * @param array $data - data to create
     * @param integer $id - ID of schedule
     * @return object
     */
    public function updateSchedule(array $data, $id)
    {
        return $this->model->find($id)->update($data);
    }

    /**
     * CREATE SCHEDULE
     * @param array $data - data to create
     * @return object
     */
    public function createSchedule(array $data)
    {
        return $this->model->create($data);
    }
}
