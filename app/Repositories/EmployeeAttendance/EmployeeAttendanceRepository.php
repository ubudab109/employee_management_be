<?php

namespace App\Repositories\EmployeeAttendance;

use App\Models\EmployeeAttendance;

class EmployeeAttendanceRepository implements EmployeeAttendanceInterface
{
  /**
   * @var ModelName
   */
  protected $model, $isSuperAdmin;

  public function __construct(EmployeeAttendance $model)
  {
    $this->model = $model;
    $this->isSuperAdmin = isSuperAdmin();
  }

  /**
   * List Paginate Employee Attendance
   * @param string $keyword
   * @param string $workPlaces
   * @param string $statusClock
   * @param string $date
   * @param int $show
   * @param int $branch
   * @return array
   */
  public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show, $branch)
  {
    $data = $this->model
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      ->when($this->isSuperAdmin, function ($query) use ($branch) {
        $query->when($branch != null, function ($subQuery) use ($branch) {
          $subQuery->where('branch_id', $branch);
        });
      })
      ->with('employee:id,name,email,nip')
      /* SEARCH BY KEYWORD */
      ->when($keyword != null || $keyword != '', function ($query) use ($keyword) {
        $query->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where('name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });

        /* FILTER BY WORKPLACES */
      })->when($workPlaces != null || $workPlaces != '', function ($query) use ($workPlaces) {
        $query->where('work_places', $workPlaces);
      })
      /* FILTER BY STATUS CLOCK */
      ->when($statusClock != null || $statusClock != '', function ($query) use ($statusClock) {
        $query->where('status_clock', $statusClock);
      })
      ->when($date != null || $date != '', function ($query) use ($date) {
        $query->whereDate('created_at', '=', $date);
      })->paginate($show);

    return $data;
  }


  /**
   * List Without Paginate Employee Attendance
   * @param string $keyword
   * @param string $workPlaces
   * @param string $statusClock
   * @param string $date
   * @param int $show
   * @param int $branch
   * @return array
   */
  public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date, $branch)
  {
    $data = $this->model
      ->when(!$this->isSuperAdmin, function ($query) {
        $query->where('branch_id', branchSelected('sanctum:manager')->id);
      })
      ->when($this->isSuperAdmin, function ($query) use ($branch) {
        $query->when($branch != null, function ($subQuery) use ($branch) {
          $subQuery->where('branch_id', $branch);
        });
      })
      ->with('employee:id,name,email,nip')
      /* SEARCH BY KEYWORD */
      ->when($keyword != null || $keyword != '', function ($query) use ($keyword) {
        $query->whereHas('employee', function ($subQuery) use ($keyword) {
          $subQuery->where('name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('email', 'LIKE', '%' . $keyword . '%')
            ->orWhere('nip', 'LIKE', '%' . $keyword . '%');
        });

        /* FILTER BY WORKPLACES */
      })->when($workPlaces != null || $workPlaces != '', function ($query) use ($workPlaces) {
        $query->where('work_places', $workPlaces);
      })
      /* FILTER BY STATUS CLOCK */
      ->when($statusClock != null || $statusClock != '', function ($query) use ($statusClock) {
        $query->where('status_clock', $statusClock);
      })
      ->when($date != null || $date != '', function ($query) use ($date) {
        $query->whereDate('created_at', '=', $date);
      })->get();

    return $data;
  }

  /**
   * Detail Attendance Employee
   * @param int $id
   * @return Object
   */
  public function detailEmployeeAttendance($id)
  {
    return $this->model->with('employee:id,name,email,nip')->with('files')->with('attendanceLocation.files')->findOrFail($id);
  }
}
