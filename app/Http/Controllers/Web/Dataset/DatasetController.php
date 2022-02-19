<?php

namespace App\Http\Controllers\Web\Dataset;

use App\Http\Controllers\BaseController;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Support\Facades\DB;

class DatasetController extends BaseController
{

    public $roleManager;

    public function __construct(RolePermissionManagerInterface $roleManager)
    {
        $this->roleManager = $roleManager;
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
}
