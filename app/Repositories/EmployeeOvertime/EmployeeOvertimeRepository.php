<?php

namespace App\Repositories\EmployeeOvertime;

use App\Models\EmployeeOvertime;
use Illuminate\Support\Facades\DB;

class EmployeeOvertimeRepository implements EmployeeOvertimeInterface
{
    /**
    * @var ModelName
    */
    protected $model, $isSuperAdmin;

    public function __construct(EmployeeOvertime $model)
    {
      $this->model = $model;
      $this->isSuperAdmin = isSuperAdmin();
    }

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
    public function listEmployeeOvertimePaginate($keyword, $department, $date, $status, $show, $employeeId = null, $branch = null)
    {
      $data = $this->model
      ->with('employee:id,firstname,lastname,nip','department:id,division_name','branch:id,branch_name')
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
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%'. $keyword .'%')
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
      ->when(isset($status) && $status != '', function ($query) use ($status) {
        $query->where('status', $status);
      })
      ->paginate($show);

      return $data;
    }

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
    public function listEmployeeOvertime($keyword, $department, $date, $status, $employeeId = null, $branch = null)
    {
      $data = $this->model
      ->with('employee:id,firstname,lastname,nip','department:id,division_name','branch:id,branch_name')
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
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%'. $keyword .'%')
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
        $query->whereDate('date', '=', $date);
      })
      /* FILTER BY EMPLOYEE ID */
      ->when(!is_null($employeeId), function ($query) use ($employeeId) {
        $query->where('employee_id', $employeeId);
      })
      /* FILTER STATUS */
      ->when(isset($status) && $status != null, function ($query) use ($status) {
        $query->where('status', $status);
      })
      ->get();

      return $data;
    }

    /**
     * DETAIL EMPLOYEE OVERTIME
     * @param integer $id
     * @return object
     */
    public function detailEmployeeOvertime($id)
    {
      $data = $this->model->with('employee:id,firstname,lastname,nip','department:id,division_name','branch:id,branch_name', 'files:id,files,source_id')
      ->find($id);
      return $data;
    }

    /**
     * CREATE OVERTIME EMPLOYEE
     * @param array $data
     * @return object
     */
    public function createEmployeeOvertime(array $data)
    {
      return $this->model->create($data);      
    }

    /**
     * UPDATE OVERTIME EMPLOYEE
     * @param array $data
     * @param integer $id
     * @return bool
     */
    public function updateEmployeeOvertime(array $data, $id)
    {
      return $this->model->find($id)->update($data);
    }

    /**
     * DELETE OVERTIME EMPLOYEE
     * @param integer $id
     * @return bool
     */
    public function deleteEmployeeOvertime($id)
    {
      return $this->model->findOrFail($id)->delete();
    }
}
