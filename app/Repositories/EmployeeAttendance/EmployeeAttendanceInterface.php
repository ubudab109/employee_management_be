<?php

namespace App\Repositories\EmployeeAttendance;

interface EmployeeAttendanceInterface
{

  /**
   * List Paginate Employee Attendance
   * @param string $keyword
   * @param string $workPlaces
   * @param string $statusClock
   * @param string $date
   * @param int $show
   * @param int $branch
   * @return array
   */
  public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show, $branch = null);

  /**
   * List Without Paginate Employee Attendance
   * @param string $keyword
   * @param string $workPlaces
   * @param string $statusClock
   * @param string $date
   * @param int $show
   * @param int $branch
   * @return array
   */
  public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date, $branch = null);

  /**
   * Detail Attendance Employee
   * @param int $id
   * @return Object
   */
  public function detailEmployeeAttendance($id);

  /**
   * Update Attendance Employee
   * @param int $id
   * @return boolean
   */
  public function updateEmployeeAttendance($data, $id);
  
}
