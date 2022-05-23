<?php

namespace App\Repositories\EmployeeAttendance;

use App\Models\EmployeeAttendance;

class EmployeeAttendanceRepository implements EmployeeAttendanceInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(EmployeeAttendance $model)
    {
      $this->model = $model;
    }

    /**
     * List Paginate Employee Attendance
     * @param string $keyword
     * @param string $workPlaces
     * @param string $statusClock
     * @param string $date
     * @param int $show
     * @return array
     */
    public function listEmployeeAttendancePaginate($keyword, $workPlaces, $statusClock, $date, $show)
    {
      $data = $this->model
      ->with('employee:id,name,email,nip')
      /* SEARCH BY KEYWORD */
      ->when($keyword != null || $keyword != '', function($query) use ($keyword) {
        $query->whereHas('employee', function($subQuery) use ($keyword) {
          $subQuery->where('name','LIKE','%'.$keyword.'%')
          ->orWhere('email','LIKE','%'.$keyword.'%')
          ->orWhere('nip','LIKE','%'.$keyword.'%');
        });

      /* FILTER BY WORKPLACES */
      })->when($workPlaces != null || $workPlaces != '', function ($query) use ($workPlaces) {
        $query->where('work_places', $workPlaces);
      })
      /* FILTER BY STATUS CLOCK */
      ->when($statusClock != null || $statusClock != '', function ($query) use ($statusClock) {
        $query->where('status_clock', $statusClock);
      })
      ->when($date != null || $date != '', function($query) use ($date) {
        $query->whereDate('created_at', '=', $date);
      })->paginate($show);

      return $data;
    }


    /**
     * List Paginate Employee Attendance
     * @param string $keyword
     * @param string $workPlaces
     * @param string $statusClock
     * @param string $date
     * @param int $show
     * @return array
     */
    public function listEmployeeAttendance($keyword, $workPlaces, $statusClock, $date)
    {
      $data = $this->model
      ->with('employee:id,name,email,nip')
      /* SEARCH BY KEYWORD */
      ->when($keyword != null || $keyword != '', function($query) use ($keyword) {
        $query->whereHas('employee', function($subQuery) use ($keyword) {
          $subQuery->where('name','LIKE','%'.$keyword.'%')
          ->orWhere('email','LIKE','%'.$keyword.'%')
          ->orWhere('nip','LIKE','%'.$keyword.'%');
        });

      /* FILTER BY WORKPLACES */
      })->when($workPlaces != null || $workPlaces != '', function ($query) use ($workPlaces) {
        $query->where('work_places', $workPlaces);
      })
      /* FILTER BY STATUS CLOCK */
      ->when($statusClock != null || $statusClock != '', function ($query) use ($statusClock) {
        $query->where('status_clock', $statusClock);
      })
      ->when($date != null || $date != '', function($query) use ($date) {
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