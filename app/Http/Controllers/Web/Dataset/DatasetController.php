<?php

namespace App\Http\Controllers\Web\Dataset;

use App\Http\Controllers\BaseController;
use App\Repositories\CompanyDivision\CompanyDivisionInterface;
use App\Repositories\CompanyJobStatus\CompanyJobStatusInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatasetController extends BaseController
{

    public $roleManager, $companyDivision, $companyJobStatus;

    public function __construct(
        RolePermissionManagerInterface $roleManager,
        CompanyDivisionInterface $companyDivision,
    ) {
        $this->roleManager = $roleManager;
        $this->companyDivision = $companyDivision;
    }

    /**
     * Dataset For Get List Employee
     * 
     * @return \Illuminate\Http\Response
     */
    public function employee()
    {
        $isSuperadmin = isSuperAdmin();
        $employees = DB::table('users')
            ->select('users.*', 'assign.user_id', 'assign.branch_id', 'branch.branch_name')
            ->join('user_division_assign as assign', 'users.id', '=', 'assign.user_id')
            ->join('company_branch as branch', 'branch.id' , '=', 'assign.branch_id')
            ->when(!$isSuperadmin, function ($query) {
                $query->where('assign.branch_id', branchSelected('sanctum:manager')->id);
            })
            ->get();
        $data = array();
        foreach ($employees as $employee) {
            $res = [
                'value'     => $employee->id,
                'label'     => $employee->firstname.' '. $employee->lastname . ' | ' . $employee->nip.' | '.$employee->branch_name
            ];

            array_push($data, $res);
        }
        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * Dataset For Get Role Manager
     * 
     * @return \Illuminate\Http\Response
     */
    public function roleManager(Request $request)
    {
        $roles = $this->roleManager->listRoleManager('', $request->branch_id);
        $data = array();
        foreach ($roles as $role) {
            $res = [
                'value' => $role->id,
                'label' => ucfirst($role->name) . ' | ' . $role->branch->branch_name
            ];
            array_push($data, $res);
        }

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * Detail Employee
     * @param Int $id
     * @return \Illuminate\Http\Response
     */
    public function detailEmployee($id)
    {
        $employees = DB::table('users')->select('id', 'nip', 'email', 'firstname','lastname')->where('id', $id)->first();
        return $this->sendResponse($employees, 'Data Fetched Successfully');
    }

    /**
     * List Department
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function listDepartment(Request $request)
    {
        $department = $this->companyDivision->getAllDivision($request->branch,$request->keyword);
        return $this->sendResponse($department, 'Data Fetched Successfully');
    }

    /**
     * Dataset Company Branch
     * 
     * @return \Illuminate\Http\Response
     */
    public function listCompanyBranch()
    {
        $branchs = DB::table('company_branch')->select('id', 'branch_name', 'branch_code')->get();
        $data = array();
        foreach ($branchs as $branch) {
            $res = [
                'value' => $branch->id,
                'label' => $branch->branch_name . ' | ' . $branch->branch_code,
            ];
            array_push($data, $res);
        }

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * Check User BY Email or NIP
     * 
     * @param Request request The request object.
     * 
     * @return bool boolean value.
     */
    public function checkEmployee(Request $request)
    {
        if ($request->has('nip') && $request->nip != null) {
            $exists = DB::table('users')->where('nip', $request->nip)
            ->when($request->has('user_id') && $request->user_id != null, function ($query) use ($request) {
                $query->where('id','!=',$request->user_id);
            })
            ->exists();
            return $this->sendResponse([
                'type'      => 'nip',
                'result'    => $exists
            ], 'Checked Successfully');
        } else if ($request->has('email') && $request->email != null) {
            $exists = DB::table('users')->where('email', $request->email)
            ->when($request->has('user_id') && $request->user_id != null, function ($query) use ($request) {
                $query->where('id','!=',$request->user_id);
            })
            ->exists();
            return $this->sendResponse([
                'type'          => 'email',
                'result'        => $exists
            ], 'Checked Successfully');
        } else {
            return $this->sendResponse(false, 'Checked Successfully');
        }
    }
}
