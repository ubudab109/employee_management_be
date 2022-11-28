<?php

namespace App\Repositories\Employee;

use App\Models\User;

interface EmployeeInterface
{
  public function getAllEmployee($keyword, $department, $jobStatus, $employeeStatus);
  public function getPaginateEmployee($keyword, $department, $jobStatus, $employeeStatus, $show);
  public function detailEmployee($id);
  public function verifyEmployee($id);
  public function createEmployee(array $data);
  public function assignEmployeeToDepartment(User $user, $departmentId);
  public function salaryInput(User $user, array $data);
  public function attendanceCutInput(User $user, array $data);
  public function createBankAccount(array $data);
  public function deleteEmployee(array $id);
}