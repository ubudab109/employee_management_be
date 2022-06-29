<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaginationResource;
use App\Models\UserManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function logActivities(Request $request)
    {
        $activites = DB::table('user_manager_activities')
            ->select('user_manager_activities.*', 'user_manager.name', 'roles.name as role')
            ->leftJoin('user_manager', 'user_manager_activities.user_manager_id', '=', 'user_manager.id')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('user_manager.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', UserManager::class);
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id');

        if ($request->has('date') && $request->date != '') {
            $activites->whereDate('date', $request->date);
        } else {
            $activites->whereDate('date', Date::now());
        }

        $res = new PaginationResource($activites->paginate($request->has('show') && $request->show != '' ? $request->show : 10));
        return $this->sendResponse($res, 'Activites Successfully Fetched');
    }

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

    public function getChartWorkplacesEmployee()
    {
        $isSuperadmin = isSuperAdmin();

        $totalOffice = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('work_places', OFFICE_PLACE)
            ->count();

        $totalRemote = DB::table('employee_attendance')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('branch_id', branchSelected('sanctum:manager')->id);
            })
            ->where('work_places', REMOTE)
            ->count();
        return $this->sendResponse([
            $totalOffice,
            $totalRemote
        ], 'Data Fetched Successfully');
    }
}
