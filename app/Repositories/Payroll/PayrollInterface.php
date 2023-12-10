<?php

namespace App\Repositories\Payroll;

interface PayrollInterface
{
	/**
	 * LIST PAYROLL
	 * @param string $keyword
	 * @param object $date
	 * @param integer $department
	 * @return Collection
	 */
	public function listPayroll($keyword, $date, $department);
	
	/**
	 * DETAIL PAYROLL FROM EMPLOYEE
	 * @param integer $id - id of employee
	 * @param array $param - filter payslip
	 * @return object
	 */
	public function detailPayroll($id, $param = []);

	/**
	 * STORING NEW PAYROLL
	 * @param array $data - data to insert
	 * @return object
	 */
	public function storePayroll(array $data);

	/**
	 * STORING NEW OR UPDATE PAYROLL
	 * @param array $param - param to update
	 * @param array $data - data to insert
	 * @return object
	 */
	public function storeOrUpdate(array $param, array $data);

	/**
	 * UPDATE EXISTING PAYROLL
	 * @param array $data - data to update
	 * @param integer $id - id payroll
	 * @return object
	 */
	public function updatePayroll(array $data, $id);
	

}