<?php

namespace App\Repositories\CompanySchedule;

interface CompanyScheduleInterface
{
    /**
     * LIST HISTORY SCHEDULE COMPANY BRANCH
     * @param integer $branchId - Selected Branch
     * @return array
     */
    public function listSchedule($branchId);

    /**
     * GET DEFAULT SCHEDULE COMPANY BRANCH
     * @param $branchId - Selected Branch
     * @return object
     */
    public function getDefaultSchedule($branchId);

    /**
     * DETAIL SCHEDULE
     * @param integer $id - ID Schedule
     * @return object
     */
    public function detailSchedule($id);

    /**
     * UPDATE SCHEDULE
     * @param array $data - data to create
     * @param integer $id - ID of schedule
     * @return bool
     */
    public function updateSchedule(array $data, $id);

    /**
     * CREATE SCHEDULE
     * @param array $data - data to create
     * @return object
     */
    public function createSchedule(array $data);
}