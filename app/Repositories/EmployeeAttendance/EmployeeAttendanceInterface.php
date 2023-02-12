<?php

namespace App\Repositories\EmployeeAttendance;

interface EmployeeAttendanceInterface
{
  public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show, $branch = null);
  public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date, $branch = null);
  public function detailEmployeeAttendance($id);
  public function updateEmployeeAttendance($data, $id);
}