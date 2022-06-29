<?php

namespace App\Repositories\EmployeeAttendance;

interface EmployeeAttendanceInterface
{
  public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show, $branch);
  public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date, $branch);
  public function detailEmployeeAttendance($id);
}