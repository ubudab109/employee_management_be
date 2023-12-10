<?php

namespace App\Repositories\EmployeeLeave;

use App\Models\EmployeeLeave;
use Illuminate\Support\Facades\DB;

class EmployeeLeaveRepository implements EmployeeLeaveInterface
{
  /**
   * @var ModelName
   */
  protected $model, $isSuperAdmin;

  public function __construct(EmployeeLeave $model)
  {
    $this->model = $model;
    $this->isSuperAdmin = isSuperAdmin();
  }

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
  public function listEmployeePaidLeavePaginate($keyword, $department, $date, $status, $paidType, $show, $employeeId = null, $branch = null)
  {
    $data = $this->model
      ->with('employee:id,firstname,lastname,nip', 'department:id,division_name', 'branch:id,branch_name')
      ->where('type', $paidType)
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      ->when($this->isSuperAdmin, function ($query) use ($branch) {
        $query->when(!is_null($branch), function ($subQuery) use ($branch) {
          $subQuery->where('branch_id', $branch);
        });
      })
      /** SEARCH KEYWORD */
      ->when(!is_null($keyword), function ($query) use ($keyword) {
        $query->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });
      })
      /** FILTER DEPARTMENT */
      ->when(!is_null($department), function ($query) use ($department) {
        $query->where('department_id', $department);
      })
      /* FILTER DATE */
      ->when($date != null || $date != '', function ($query) use ($date) {
        $query->whereDate('date ', '=', $date);
      })
      /* FILTER BY EMPLOYEE ID */
      ->when(!is_null($employeeId), function ($query) use ($employeeId) {
        $query->where('employee_id', $employeeId);
      })
      /* FILTER STATUS */
      ->when(isset($status) && $status != null, function ($query) use ($status) {
        $query->where('status', $status);
      })
      ->paginate($show);

    return $data;
  }

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
  public function listEmployeePaidLeave($keyword, $department, $date, $status, $paidType, $employeeId = null, $branch = null)
  {
    $data = $this->model
      ->with('employee:id,firstname,lastname,nip', 'department:id,division_name', 'branch:id,branch_name')
      ->where('type', $paidType)
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      ->when($this->isSuperAdmin, function ($query) use ($branch) {
        $query->when(!is_null($branch), function ($subQuery) use ($branch) {
          $subQuery->where('branch_id', $branch);
        });
      })
      /** SEARCH KEYWORD */
      ->when(!is_null($keyword), function ($query) use ($keyword) {
        $query->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });
      })
      /** FILTER DEPARTMENT */
      ->when(!is_null($department), function ($query) use ($department) {
        $query->where('department_id', $department);
      })
      /* FILTER DATE */
      ->when($date != null || $date != '', function ($query) use ($date) {
        $query->whereDate('date ', '=', $date);
      })
      /* FILTER BY EMPLOYEE ID */
      ->when(!is_null($employeeId), function ($query) use ($employeeId) {
        $query->where('employee_id', $employeeId);
      })
      /* FILTER STATUS */
      ->when(isset($status) && $status != null, function ($query) use ($status) {
        $query->where('status', $status);
      })->get();

    return $data;
  }

  /**
   * DETAIL EMPLOYEE PAID LEAVE
   * @param integer $id
   * @return object
   */
  public function detailEmployeePaidLeave($id)
  {
    $data = $this->model
      ->with('employee', 'department:id,division_name', 'branch:id,branch_name', 'files:id,files,source_id')->find($id);
    return $data;
  }

  /**
   * CREATE NEW EMPLOYEE PAID LEAVE
   * @param array $data
   * @return object
   */
  public function createEmployeePaidLeave(array $data)
  {
    return $this->model->create($data);
  }

  /**
   * UPDATE EXISTING EMPLOYEE PAID LEAVE BY ID
   * @param integer $id
   * @return bool
   */
  public function updateEmployeePaidLeave(array $data, $id)
  {
    return $this->model->find($id)->update($data);
  }

  /**
   * DELETE EXISTING EMPLOYEE PAID LEAVE BY ID
   * @param integer $id
   * @return bool
   */
  public function deleteEmployeePaidLeave($id)
  {
    return $this->model->find($id)->delete();
  }
}
