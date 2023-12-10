<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\BaseController;
use App\Models\EmployeeLeave;
use App\Models\UserManager;
use App\Models\UserManagerAssign;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    /**
     * GET HEAD BRANCH IN CURRENT BRANCH
     */
    public function getHeadBranch()
    {
        $branchId = branchSelected('sanctum:manager')->id;
        $managerAssign = DB::table('user_manager_assign as manager')
            ->join('model_has_roles as mr', 'mr.model_id', 'manager.id')
            ->join('roles', 'roles.id', 'mr.role_id')
            ->join('user_manager', 'user_manager.id', 'manager.user_manager_id')
            ->where('roles.is_headbranch', 1)
            ->where('mr.model_type', UserManagerAssign::class)
            ->where('manager.user_manager_id', Auth::guard('sanctum:manager')->user()->id)
            ->where('manager.branch_id', $branchId)
            ->first();

        return $this->sendResponse($managerAssign, 'Data Fetched Successfully');
    }

    /**
     * GET DATA EMPLOYEE BASED ON BRANCH 
     * 
     * @return Illuminate\Http\Response
     */
    public function getChartEmployee()
    {
        $isSuperadmin = isSuperAdmin();
        $totalMale = DB::table('users')
            ->join('user_division_assign as assign', 'assign.user_id', '=', 'users.id')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('assign.branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('gender', 'male')->count();
        $totalFemale = DB::table('users')
            ->join('user_division_assign as assign', 'assign.user_id', '=', 'users.id')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('assign.branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('gender', 'female')->count();
        return $this->sendResponse([
            $totalMale,
            $totalFemale,
        ], 'Data Fetched Successfully');
    }

    /**
     * GET DATA ATTENDANCE EMPLOYEE TODAY BASED ON BRANCH ID
     * 
     * @return Illuminate\Http\Response
     */
    public function getAttendance()
    {
        $isSuperadmin = isSuperAdmin();
        $countTotalEmployee = DB::table('users')
            ->join('user_division_assign as employee', 'employee.user_id', 'users.id')
            ->where('employee.branch_id', branchSelected('sanctum:manager')->id)
            ->count();

        $totalOntime = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->whereDate('date', '=', date('Y-m-d', strtotime(now())))
            ->where('status_clock', ON_TIME)
            ->count();
        $totalLate = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->whereDate('date', '=', date('Y-m-d', strtotime(now())))
            ->where('status_clock', LATE)
            ->count();

        $totalAbsent = $countTotalEmployee - DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->whereDate('date', '=', date('Y-m-d', strtotime(now())))
            ->count();

        $totalPaidLeave = DB::table('employee_leave')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('type', PAID_LEAVE)
            ->where('status', LEAVE_APPROVE)
            ->get()->filter(function ($item) {
                if (Carbon::now()->between($item->start_date, $item->end_date)) return $item;
            });

        $totalPermit = DB::table('employee_leave')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('type', PERMIT)
            ->get()->filter(function ($item) {
                if (Carbon::now()->between($item->start_date, $item->end_date)) return $item;
            });

        $mainSchedule = DB::table('company_schedule')
            ->select('clock_in', 'clock_out', 'is_default')
            ->where('branch_id', branchSelected('sanctum:manager')->id)
            ->where('is_default', 1)
            ->first();

        return $this->sendResponse([
            'main_schedule' => $mainSchedule,
            'graph'         => [
                $totalOntime,
                $totalLate,
                $totalAbsent,
                count($totalPaidLeave->toArray()),
                count($totalPermit->toArray()),
                $countTotalEmployee,
            ]
        ], 'Data Fetched Successfully');
    }

    /**
     * GET ATTENDANCE WORKPLACES BASED ON BRANCH
     * 
     * @return Illuminate\Http\Response
     */
    public function getChartWorkplacesEmployee()
    {
        $isSuperadmin = isSuperAdmin();

        $totalOffice = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->whereDate('date', '=', date('Y-m-d', strtotime(now())))
            ->where('work_places', OFFICE_PLACE)
            ->count();

        $totalRemote = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->whereDate('date', '=', date('Y-m-d', strtotime(now())))
            ->where('work_places', REMOTE)
            ->count();

        return $this->sendResponse([
            $totalOffice,
            $totalRemote,
        ], 'Data Fetched Successfully');
    }

    /**
     * GET USER MANAGER BASED ON BRANCH
     */
    public function getUserManager()
    {
        $manager = UserManager::whereHas('branchAssign', function ($subQuery) {
            $subQuery->where('branch_id', branchSelected('sanctum:manager')->id);
        })
            ->with('branchAssign')
            ->get();

        return $this->sendResponse($manager, "Data Fetched Successfully");
    }
    
    /**
     * GET ALL NOTIFICATION BASE ON USER MANAGER AUTH
     * 
     * @return Illuminate\Http\Response
     */
    public function getNotification()
    {
        $notifManagerData = DB::table('notification_manager')->where('user_manager_id', Auth::guard('sanctum:manager')->user()->id)->orderBy('created_at', 'desc')->get();
        return $this->sendResponse($notifManagerData, 'Data Fetched Successfully');
    }

    /**
     * READ ALL NOTIFICATION FOR USER MANAGER
     * 
     * @return Illuminate\Http\Response
     */
    public function readAllNotification()
    {
        DB::table('notification_manager')
            ->where('user_manager_id', Auth::guard('sanctum:manager')->user()->id)
            ->update([
                'is_read' => 1,
            ]);
        return $this->sendResponse(array('success' => 1), 'Notification Had Been Read All');
    }
}
