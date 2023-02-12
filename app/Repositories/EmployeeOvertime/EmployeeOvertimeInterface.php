<?php

namespace App\Repositories\EmployeeOvertime;

interface EmployeeOvertimeInterface
{

  public function listEmployeeOvertimePaginate($keyword, $department, $date, $status, $show, $employeeId = null ,$branch = null);
  public function listEmployeeOvertime($keyword, $department, $date, $status, $employeeId = null, $branch = null);
  public function detailEmployeeOvertime($id);
  public function createEmployeeOvertime(array $data);
  public function updateEmployeeOvertime(array $data, $id);
  public function deleteEmployeeOvertime($id);
}