<?php

namespace App\Repositories\EmployeeReimbersement;

interface EmployeeReimbursementInterface
{
	/**
	 * LIST EMPLOYEE REIMBERSEMENT
	 * @param string $keyword
	 * @param object $date
	 * @param integer $employeeId
	 * @param string $status
	 * @return Collection
	 */
	public function listReimbersement($keyword, $date, $employeeId, $status);

	/**
	 * DETAIL EMPLOYEE REIMBERSEMENT
	 * @param integer $id
	 * @return object
	 */
	public function detailReimbersement($id);

	/**
	 * STORE REIMBERSEMENT DATA
	 * @param array $data
	 * @return object
	 */
	public function createReimbersement(array $data);

	/**
	 * UPDATE REIMBURSEMENT DATA
	 * @param array $data
	 * @param integer $id - id of reimbersement data
	 * @return bool
	 */
	public function updateReimbersement(array $data, $id);

}