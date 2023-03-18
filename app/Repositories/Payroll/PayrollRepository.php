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
    ->with(['payslipStatus' => function ($query) use ($date) {
      $query->where('month', $date['month'])
      ->where('years', $date['years']);
    }])
    ->whereHas('paySlip', function ($query) use ($keyword, $date) {
      $query->when($keyword !== null && $keyword !== '', function ($filter) use ($keyword) {
        $filter->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });
      })
      ->where('month', $date['month'])
      ->where('years', $date['years']);
    })
    ->withSum(['paySlip' => function ($query) use ($date) {
      $query->where('month', $date['month'])
      ->where('years', $date['years']);
    }], 'amount')
    ->get();
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
      ->where('month', $param['month'])->where('years', $param['years']);
    $totalPresent = DB::table('employee_attendance')
    ->where('employee_id', $employee->id)
    ->whereMonth('date', $param['month'])
    ->whereYear('date', $param['years']);
    $leave = DB::table('employee_leave')
    ->where('employee_id', $employee->id)
    ->whereMonth('start_date', $param['month'])
    ->whereYear('start_date', $param['years']);

    return [
      'presence' => [
        'total_present'    => $totalPresent->whereIn('status_clock', [ON_TIME, LATE])->count(),
        'total_late'       => $totalPresent->where('status_clock', LATE)->count(),
        'total_paid_leave' => $leave->where('type', PAID_LEAVE)->sum('taken'),
        'total_permit'     => $leave->where('type', PERMIT)->sum('taken'),
      ],
      'total_salary' => $paySlip->sum('amount'),
      'employee' => $employee,
      'payslip'  => $paySlip->get()
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
