<?php

namespace App\Repositories\EmployeeAttendance;

interface EmployeeAttendanceInterface
{
  public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show);
  public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date);
  public function detailEmployeeAttendance($id);
}