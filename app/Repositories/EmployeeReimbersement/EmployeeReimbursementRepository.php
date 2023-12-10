<?php

namespace App\Repositories\EmployeeReimbersement;

use App\Models\EmployeeReimburshment;
use Illuminate\Support\Facades\DB;

class EmployeeReimbursementRepository implements EmployeeReimbursementInterface
{
  /**
   * @var ModelName
   */
  protected $model, $isSuperAdmin;

  public function __construct(EmployeeReimburshment $model)
  {
    $this->model = $model;
    $this->isSuperAdmin = isSuperAdmin();
  }

  /**
   * LIST EMPLOYEE REIMBERSEMENT
   * @param string $keyword
   * @param object $date
   * @param integer $employeeId
   * @param integer $claimTypeId
   * @param string $status
   * @return Collection
   */
  public function listReimbersement($keyword, $date, $employeeId, $claimTypeId, $status)
  {
    return $this->model
      ->with('employee', 'files')
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      /** SEARCH KEYWORD */
      ->when(!is_null($keyword), function ($query) use ($keyword) {
        $query->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });
      })
      /* FILTER DATE */
      ->when($date != null || $date != '', function ($query) use ($date) {
        $query->whereDate('date', '=', $date);
      })
      /* FILTER BY EMPLOYEE ID */
      ->when(!is_null($employeeId), function ($query) use ($employeeId) {
        $query->where('employee_id', $employeeId);
      })
      /* FILTER CLAIM TYPE */
      ->when(!is_null($claimTypeId), function ($query) use ($claimTypeId) {
        $query->where('claim_type_id', $claimTypeId);
      })
      /* FILTER STATUS */
      ->when(isset($status) && $status != 'All', function ($query) use ($status) {
        $query->where('status', $status);
      })
      ->get();
  }

  /**
   * DETAIL EMPLOYEE REIMBERSEMENT
   * @param integer $id
   * @return object
   */
  public function detailReimbersement($id)
  {
    return $this->model->with('employee', 'files', 'claimType:id,name')->find($id);
  }

  /**
   * STORE REIMBERSEMENT DATA
   * @param array $data
   * @return object
   */
  public function createReimbersement(array $data)
  {
    return $this->model->create($data);
  }

  /**
   * UPDATE REIMBURSEMENT DATA
   * @param array $data
   * @param integer $id - id of reimbersement data
   * @return bool
   */
  public function updateReimbersement(array $data, $id)
  {
    return $this->model->find($id)->update($data);
  }

}
