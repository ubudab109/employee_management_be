<?php

namespace App\Repositories\EmployeeOvertime;

interface EmployeeOvertimeInterface
{

  /**
   * LIST EMPLOYEE OVERTIME WITH PAGINATE
   * @param string $keyword
   * @param integer $department
   * @param string $data
   * @param string $status
   * @param integer $show
   * @param integer $branch
   * @return Collection
   */
  public function listEmployeeOvertimePaginate($keyword, $department, $date, $status, $show, $employeeId = null, $branch = null);

  /**
   * LIST EMPLOYEE OVERTIME WITHOUT PAGINATE
   * @param string $keyword
   * @param integer $department
   * @param string $data
   * @param string $status
   * @param integer $show
   * @param integer $branch
   * @return Collection
   */
  public function listEmployeeOvertime($keyword, $department, $date, $status, $employeeId = null, $branch = null);

  /**
   * DETAIL EMPLOYEE OVERTIME
   * @param integer $id
   * @return object
   */
  public function detailEmployeeOvertime($id);

  /**
   * CREATE OVERTIME EMPLOYEE
   * @param array $data
   * @return object
   */
  public function createEmployeeOvertime(array $data);

  /**
   * UPDATE OVERTIME EMPLOYEE
   * @param array $data
   * @param integer $id
   * @return bool
   */
  public function updateEmployeeOvertime(array $data, $id);

  /**
   * DELETE OVERTIME EMPLOYEE
   * @param integer $id
   * @return bool
   */
  public function deleteEmployeeOvertime($id);
  
}
