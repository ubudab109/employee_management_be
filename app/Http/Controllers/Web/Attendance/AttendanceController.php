<?php

namespace App\Http\Controllers\Web\Attendance;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Repositories\EmployeeAttendance\EmployeeAttendanceInterface;
use App\Services\AttendanceServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends BaseController
{
    public $services;

    public function __construct(AttendanceServices $services)
    {
        $this->services = $services;
        $this->middleware('userpermissionmanager:attendance-management-list', ['only' => 'index']);
        $this->middleware('userpermissionmanager:attendance-management-detail', ['only' => 'detail']);
        $this->middleware('userpermissionmanager:attendance-management-edit', ['only' => 'update']);
    }

    /**
     * List Attendance Employee
     * 
     * @param Illuminate\Http\Request
     * @return Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $param = [
            $request->keyword,
            $request->workPlaces,
            $request->statusClock,
            $request->has('date') && $request->date != '' ? $request->date : Date::now(),
            $request->show,
            $request->branch_id
        ];

        $data = $this->services->index($param);

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * List Attendance Employee
     * 
     * @param App\Models\EmployeeAttendance $id
     * @return Illuminate\Http\Response
    */
    public function show($id)
    {
        $data = $this->services->detail($id);
        return $this->sendResponse([
            'shift_time'    => allCompanySetting('company_entry_hours'). '-' . allCompanySetting('company_out_hours'),
            'data'          => $data['data'],
        ], 'Data Fetched Successfully');
    }

    /**
     * Update Attendance
     * 
     * @param Request $request
     * @param int $id
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'work_places'   => 'required',
            'status_clock'  => 'required',
            'clock_in'      => '',
            'clock_out'     => '',
            'date'          => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        $data = $this->services->updateAttendance($request->all(), $id);

        if (!$data['status']) {
            return $this->sendError($data['message']);
        }

        return $this->sendResponse(array('success' => 1), $data['message']);
    }
}
