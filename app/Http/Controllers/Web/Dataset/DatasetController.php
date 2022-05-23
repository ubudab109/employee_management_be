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
        CompanyJobStatusInterface $companyJobStatus
    )
    {
        $this->roleManager = $roleManager;
        $this->companyDivision = $companyDivision;
        $this->companyJobStatus = $companyJobStatus;
    }

    /**
     * Dataset For Get List Employee
     * 
     * @return \Illuminate\Http\Response
     */
    public function employee()
    {
        $employees = DB::table('users')->get();
        $data = array();
        foreach($employees as $employee) {
            $res = [
                'value'     => $employee->id,
                'label'     => $employee->name.' | '.$employee->nip
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
    public function roleManager()
    {
        $roles = $this->roleManager->listRoleManager('');
        $data = array();
        foreach ($roles as $role) {
            $res = [
                'value' => $role->id,
                'label' => ucfirst($role->name)
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
        $employees = DB::table('users')->select('id','nip','email','name')->where('id', $id)->first();
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
        $department = $this->companyDivision->getAllDivision($request->keyword);
        return $this->sendResponse($department, 'Data Fetched Successfully');
    }

    /**
     * List Job Status
     * 
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
    */
    public function listJobStatus(Request $request)
    {
        $jobStatus = $this->companyJobStatus->getAllJobStatus($request->keyword);
        return $this->sendResponse($jobStatus, 'Data Fetched Successfully');
    }
}
