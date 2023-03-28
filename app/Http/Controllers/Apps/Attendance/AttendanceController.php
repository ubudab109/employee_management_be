<?php

namespace App\Http\Controllers\Apps\Attendance;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\CompanyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends BaseController
{
    public function clockInScan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userLat'     => 'required',
            'userLong'    => 'required',
            'work_places' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $branchSelected = DB::table('company_branch')->find(branchSelected('sanctum:employee')->id);
            $param = [
                'userLat'    => $request->input('lat'),
                'userLong'   => $request->input('lang'),
                'branchLat'  => $branchSelected->latitude,
                'branchLong' => $branchSelected->longitude,
            ];
            $data = [
                'employee_id' => Auth::guard('sanctum:employee')->id(),
                'branch_id'   => $branchSelected->id,
                'work_places' => $request->input('work_places'),
            ];
            DB::table('employee_attendance')->insertGetId([

            ]);
        } catch (\Exception $err) {

        }

    }
}
