<?php

namespace App\Http\Controllers\Web\Attendance;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceInterface;
use App\Services\MapServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class AttendanceController extends BaseController
{
    public $employeeAttendance;

    public function __construct(EmployeeAttendanceInterface $employeeAttendance)
    {
        $this->employeeAttendance = $employeeAttendance;
        $this->middleware('userpermissionmanager:attendance-management-list', ['only' => 'index']);
        $this->middleware('userpermissionmanager:attendance-management-detail', ['only' => 'detail']);
    }

    /**
     * List Attendance Employee
     * 
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        if ($request->has('show') && $request->show != null) {
            $data = $this->employeeAttendance->listEmployeeAttendancePaginate(
                $request->keyword,
                $request->workPlaces,
                $request->statusClock,
                $request->has('date') && $request->date != '' ? $request->date : Date::now(),
                $request->show,
            );
            $res = new PaginationResource($data);
        } else {
            $res = $this->employeeAttendance->listEmployeeAttendance(
                $request->keyword,
                $request->workPlaces,
                $request->statusClock,
                $request->has('date') && $request->date != '' ? $request->date : Date::now(),
            );
        }


        return $this->sendResponse($res, 'Data Fetched Successfully');
    }

    /**
     * List Attendance Employee
     * 
     * @param App\Models\EmployeeAttendance $id
     * @return Illuminate\Http\Response
    */
    public function detail($id)
    {
        $data = $this->employeeAttendance->detailEmployeeAttendance($id);
        return $this->sendResponse([
            'shift_time'    => allCompanySetting('company_entry_hours'). '-' . allCompanySetting('company_out_hours'),
            'data'          => $data,
        ], 'Data Fetched Successfully');
    }
}
