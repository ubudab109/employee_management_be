<?php

namespace App\Services;

use App\Http\Resources\PaginationResource;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceInterface;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceServices
{
  public $attendance;

  public function __construct(EmployeeAttendanceInterface $attendance)
  {
    $this->attendance = $attendance;
  }

  /**
   * It fetches the employee attendance list from the database
   * 
   * @param array $param
   */
  public function index($param = [])
  {
    if (isset($param['show']) && $param['show'] != null) {
      $data = $this->attendance->listEmployeeAttendancePaginate(
        isset($param['keyword']) ? $param['keyword'] : null,
        isset($param['workPlaces']) ? $param['workPlaces'] : null,
        isset($param['statusClock']) ? $param['statusClock'] : null,
        isset($param['date']) ? $param['date'] :  Date::now(),
        $param['show'],
        isset($param['branch_id']) ? $param['branch_id'] : null,
      );
      $res = new PaginationResource($data);
    } else {
      $res = $this->attendance->listEmployeeAttendance(
        isset($param['keyword']) ? $param['keyword'] : null,
        isset($param['workPlaces']) ? $param['workPlaces'] : null,
        isset($param['statusClock']) ? $param['statusClock'] : null,
        isset($param['date']) ? $param['date'] : Date::now(),
        isset($param['branch_id']) ? $param['branch_id'] : null,
      );
    }
    return [
      'status'    => true,
      'message'   => 'Employee Attendance List Fetched Successfully',
      'data'      => $res,
    ];
  }

  /**
   * It returns an array with a status, message, and data key
   * 
   * @param int $id The id of the attendance record you want to fetch.
   * 
   * @return array array of data.
   */
  public function detail($id)
  {
    $data = $this->attendance->detailEmployeeAttendance($id);
    if (is_null($data)) {
      return [
        'status'    => false,
        'message'   => 'Attendance Not Found',
        'data'      => null,
      ];
    }

    return [
      'status'  => true,
      'message' => 'Data Fetched Successfully',
      'data'    => $data,
    ];
  }

  /**
   * It updates the attendance data of an employee
   * 
   * @param array data array of data to be updated
   * @param int $id The id of the attendance record
   * @return array array with two keys: status and message.
   */
  public function updateAttendance(array $data, $id)
  {
    DB::beginTransaction();
    try {
      $this->attendance->updateEmployeeAttendance($data, $id);
      DB::commit();
      return [
        'status'  => true,
        'message' => 'Attendance Data Updated Successfully',
      ];
    } catch (\Exception $err) {
      DB::rollBack();
      Log::channel('test')->info($err->getMessage());
      return [
        'status'  => false,
        'message' => 'Internal Server Error',
      ];
    }
  }
}
