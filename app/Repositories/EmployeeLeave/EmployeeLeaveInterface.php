<?php

namespace App\Repositories\EmployeeLeave;

interface EmployeeLeaveInterface
{

  /**
   * LIST EMPLOYEE PAID LEAVE WITH PAGINATE
   * @param string $keyword
   * @param integer $department
   * @param string $data
   * @param string $status
   * @param integer $show
   * @param integer $employeeId
   * @param integer $branch
   * @return Collection
   */
  public function listEmployeePaidLeavePaginate($keyword, $department, $date, $status, $paidType, $show, $employeeId = null, $branch = null);

  /**
   * LIST EMPLOYEE PAID LEAVE WITHOUT PAGINATE
   * @param string $keyword
   * @param integer $department
   * @param string $data
   * @param string $status
   * @param integer $employeeId
   * @param integer $branch
   * @return Collection
   */
  public function listEmployeePaidLeave($keyword, $department, $date, $status, $paidType, $employeeId = null, $branch = null);

  /**
   * DETAIL EMPLOYEE PAID LEAVE
   * @param integer $id
   * @return object
   */
  public function detailEmployeePaidLeave($id);

  /**
   * CREATE NEW EMPLOYEE PAID LEAVE
   * @param array $data
   * @return object
   */
  public function createEmployeePaidLeave(array $data);

  /**
   * UPDATE EXISTING EMPLOYEE PAID LEAVE BY ID
   * @param integer $id
   * @return bool
   */
  public function updateEmployeePaidLeave(array $data, $id);

  /**
   * DELETE EXISTING EMPLOYEE PAID LEAVE BY ID
   * @param integer $id
   * @return bool
   */
  public function deleteEmployeePaidLeave($id);
}
