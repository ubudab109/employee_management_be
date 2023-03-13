<?php

namespace App\Repositories\Payroll;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PayrollRepository implements PayrollInterface
{
  /**
   * @var ModelName
   */
  protected $model, $employee;

  public function __construct(Payroll $model, User $employee)
  {
    $this->model = $model;
    $this->employee = $employee;
  }

  /**
   * LIST PAYROLL
   * @param string $keyword
   * @param object $date
   * @param integer $department
   * @return Collection
   */
  public function listPayroll($keyword, $date, $department)
  {
    $data = $this->employee
    ->when($department !== null, function ($query) use ($department) {
      $query->whereHas('division', function ($subQuery) use ($department) {
        $subQuery->where('division_id', $department);
      });
     })
    ->whereHas('paySlip', function ($query) use ($keyword, $date, $department) {
      $query->when($keyword !== null && $keyword !== '', function ($filter) use ($keyword, $department) {
        $filter->whereHas('employee', function ($subQuery) use ($keyword, $department) {
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });
      })
      ->where('month', $date['month'])
      ->where('years', $date['years']);
    })->get();
    return $data;
  }

  /**
   * DETAIL PAYROLL FROM EMPLOYEE
   * @param integer $id - id of employee
   * @param array $param - filter payslip
   * @return object
   */
  public function detailPayroll($id, $param = [])
  {
    $employee = $this->employee->find($id);
    $paySlip = $this->model->where('employee_id', $employee->id)
      ->where('month', $param['month'])->where('years', $param['years'])->get();
    return [
      'employee' => $employee,
      'payslip'  => $paySlip
    ];
  }

  /**
   * STORING NEW PAYROLL
   * @param array $data - data to insert
   * @return object
   */
  public function storePayroll(array $data)
  {
    return $this->model->create($data);
  }

  /**
   * STORING NEW OR UPDATE PAYROLL
   * @param array $param - param to update
   * @param array $data - data to insert
   * @return object
   */
  public function storeOrUpdate(array $param, array $data)
  {
    return $this->model->updateOrCreate($param, $data);
  }

  /**
   * UPDATE EXISTING PAYROLL
   * @param array $data - data to update
   * @param integer $id - id payroll
   * @return object
   */
  public function updatePayroll(array $data, $id)
  {
    return $this->model->find($id)->update($data);
  }
}
