<?php

namespace App\Repositories\Employee;

use App\Models\User;

interface EmployeeInterface
{
  
  /**
   * GET EMPLOYEE DATA WITHOUT PAGINATE
   * 
   * @param string $keyword — searching by keyword like name, email, nip or phone number
   * @param int $department — filter by departement or division id
   * @param int $jobStatus — filter by job status like full time, freelance or etc. According to company job status
   * @param string $employeeStatus — filter by status employee like active ('1'), inactive ('0'), pending ('2')
   * @return Collection
   */
  public function getAllEmployee($keyword, $department, $jobStatus, $employeeStatus);

  /**
   * GET EMPLOYEE DATA WITH PAGINATE
   * 
   * @param string $keyword — searching by keyword like name, email, nip or phone number
   * @param int $department — filter by departement or division id
   * @param int $jobStatus — filter by job status like full time, freelance or etc. According to company job status
   * @param string $employeeStatus — filter by status employee like active ('1'), inactive ('0'), pending ('2')
   * @param int $show — total data per page
   * @return Collection
   */
  public function getPaginateEmployee($keyword, $department, $jobStatus, $employeeStatus, $show);

  /**
   * DETAIL EMPLOYEE
   * 
   * @param int $id - ID from employee or users
   * @param string $param - Detail Type
   * @param array $request - Array of Request
   * @return Collection
   */
  public function detailEmployee($id, $param = null, $request = []);

  /**
   * VERIFY EMAIL USER
   * 
   * @param int $id - ID from employee or users
   * @return \App\Models\User
   */
  public function verifyEmployee($id);

  /**
   * It creates an employee and returns the employee
   * 
   * @param array data - data to input
   * 
   * @return object employee object
   */
  public function createEmployee(array $data);

  /**
   * It takes an array of data and an id, and then it updates the model with the given id with the
   * given data
   * 
   * @param array data The data to be updated
   * @param integer id The id of the employee you want to update
   * 
   * @return object.
   */
  public function updateEmployee(array $data, $id);

  /**
   * Updating assigned branch from spesific user or employee
   * 
   * @param array $data - The data to be updated
   * @param integer $id - The id of the employee you want to update
   * 
   * @return object
   */
  public function updateBranchEmployee(array $data, $id);

  /**
   * It assigns a user to a division
   * 
   * @param User user The user object
   * @param integer $divisionId The id of the division you want to assign the user to.
   * 
   * @return object new instance of the UserDivision model.
   */
  public function assignEmployeeToDepartment(User $user, $departmentId);

  /**
   * Create a new salary record for the given user.
   * 
   * @param User user The user model instance
   * @param array data
   * 
   * @return object salaryInput method is returning the salary record that was created.
   */
  public function salaryInput(User $user, array $data);

  /**
   * Create a new attendance cut record for the given user.
   * 
   * @param array data 
   * 
   * @return object
   */
  public function attendanceCutInput(User $user, array $data);

  /**
   * It creates a new bank account for a user
   * 
   * @param array data This is the array of data that you want to insert into the database.
   * 
   * @return object
   */
  public function createBankAccount(array $data);

  /**
   * It deletes the employee with the given id
   * 
   * @param array id The id of the employee to be deleted
   * 
   * @return array return value is the number of rows affected by the delete query.
   */
  public function deleteEmployee(array $id);

  /**
   * UPDATE FINANCE EMPLOYEE
   * INCLUDE: PAYMENT DATE, BANK, SALARY, ATTENDANCE CUT
   * @param array $data
   * @param string $type - type of updating data
   * @param int $id id employee
   * @return bool
   */
  public function updateFinanceEmployee(array $data, $type, $id);

}
