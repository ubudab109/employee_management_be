<?php

namespace App\Http\Controllers\Web\Dataset;

use App\Http\Controllers\BaseController;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeReimburshment;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\User;
use App\Models\UserManagerAssign;
use App\Repositories\CompanyDivision\CompanyDivisionInterface;
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
     * GET EMPLOYEE DATA THAT DOESN'T HAVE A PAYSLIP IN SEPESIFIC MONTH AND YEAR
     * @param Request $request
     * @return Response
     */
    public function employeeNotInPayslip(Request $request)
    {
        $employees = User::whereHas('branch', function ($query) {
            $query->where('branch_id', branchSelected('sanctum:manager')->id);
        })->whereDoesntHave('paySlip', function ($query) use ($request) {
            $query->where('month', $request->month)
            ->where('years', $request->years);
        })->get();
        $data = [];
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
     * GET ROLE DATA BY BRANCH ID
     * @param Request $request
     * @return Response
     */
    public function getRoleByBranch(Request $request)
    {
        $roles = DB::table('roles');

        if (!empty($request->branch_id)) {
            $roles->where('branch_id', $request->branch_id);
        }

        if ($request->has('filter') && $request->filter == 1) {
            $data = [];
            foreach ($roles->get() as $role) {
                $data[] = [
                    'label' => $role->name,
                    'value' => $role->id,
                ];
            }
        } else {
            $data = $roles;
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
        if ($request->get('filter') == 1) {
            $department = DB::table('company_division')->where('branch_id', branchSelected('sanctum:manager')->id)
            ->select('id as value', 'division_name as label')->get();
        } else {
            $department = $this->companyDivision->getAllDivision($request->branch,$request->keyword);
        }
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

    /**
     * SALARY COMPONENT DATASET
     * 
     * @param Request
     * @return Collection
     */
    public function salaryComponent(Request $request)
    {
        $salary = DB::table('salary_component')
        ->when($request->has('type') && $request->type !== '', function ($query) use ($request) {
            $query->where('type', $request->type);
        })->orderBy('name', 'asc')->get();
        return $this->sendResponse($salary, 'Data Fetched Successfully');
    }

    /**
     * It takes the first and last day of the month, and then subtracts the number of holidays from the
     * total number of days in the month
     * 
     * @param Request $request The request object.
     * 
     * @return Response.
     */
    public function getWorkinDays(Request $request)
    {
        $holidays = DB::table('holidays')
        ->where('years', $request->get('years'))
        ->where('month', $request->get('month'))
        ->whereNotNull('data')
        ->get();

        $holidayDate = [];
        foreach ($holidays as $holiday) {
            foreach (json_decode($holiday->data, true) as $date) {
                $holidayDate[] = $date['date'];
            }
        }
        $timeStamp = strtotime(
            "".ucwords(getMonthName($request->get('month'))." $request->years"
        ));
        $firstDateMonth = date('Y-m-01', $timeStamp);
        $endDateMonth = date('Y-m-t', $timeStamp);
        $totalDays = workingDays($firstDateMonth, $endDateMonth, $holidayDate);
        return $this->sendResponse($totalDays, 'Total Days Fetched');
    }

    /**
     * DATASET CLAIM TYPE
     * @param Request $request
     * @return Response
     */
    public function listClaimType(Request $request)
    {
        if ($request->has('type') && $request->type == 'filter') {
            $data = DB::table('claim_type')->select('id as value', 'name as label')
            ->where('branch_id', branchSelected('sanctum:manager')->id)->get();
        } else {
            $data = DB::table('claim_type')
            ->where('branch_id', branchSelected('sanctum:manager')->id)->get();
        }

        return $this->sendResponse($data, 'Claim Type Dataset Fetched Successfully');
    }

    /**
     * GET HOLIDAYS IN SPESIFIC YEARS OR MONTH
     * @param Request $request
     * @return Response
     */
    public function getHolidays(Request $request)
    {
        $data = Holiday::when($request->has('years') && !is_null($request->years), function ($query) use ($request) {
            $query->where('years', $request->years);
        })
        ->when($request->has('month') && !is_null($request->month), function ($query) use ($request) {
            $query->where('month', $request->month);
        })->get();

        return $this->sendResponse($data, 'Holidays Fetched Successfully');
    }

    /**
     * GET LIST PTKP
     * @param Request $request
     * @return Illuminate\Htpp\Response
     */
    public function getPtkpData(Request $request)
    {
        if ($request->has('select') && $request->select) {
            $data = DB::table('ptkp')->select('id as value', 'status as label')->get();
        } else {
            $data = DB::table('ptkp')->get();
        }

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET EXCEL MODEL TYPE
     * THIS TYPE WHICH CAN BE EXPORTED OR IMPORTED
     * @return Illuminate\Htpp\Response
     */
    public function getExportModelType()
    {
        $sumFiles = function ($model) {
            return DB::table('excel_tasks')->where('branch_id', branchSelected('sanctum:manager')->id)
            ->where('source_type', $model)
            ->whereNotNull('download')
            ->count();
        };
        $data = [
            [
                'name'        => 'Payslip',
                'value'       => Payroll::class,
                'total_files' => $sumFiles(Payroll::class),
            ],
            [
                'name'        => 'Reimbursement',
                'value'       => EmployeeReimburshment::class,
                'total_files' => $sumFiles(EmployeeReimburshment::class),
            ],
            [
                'name'        => 'Employee',
                'value'       => User::class,
                'total_files' => $sumFiles(User::class),

            ],
            [
                'name'        => 'Employee Attendance',
                'value'       => EmployeeAttendance::class,
                'total_files' => $sumFiles(EmployeeAttendance::class),

            ],
            [
                'name'        => 'Employee Overtime',
                'value'       => EmployeeOvertime::class,
                'total_files' => $sumFiles(EmployeeOvertime::class),

            ],
        ];

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET PROVINCES DATASET
     * @return Illuminate\Htpp\Response
     */
    public function getProvinces()
    {
        $data = DB::table('provinces')->select('id as value', 'name as label')->get();
        return $this->sendResponse($data, 'Data Fetched Successfully');   
    }

    /**
     * GET REGENCIES DATASET
     * @param Request $request
     * @return Illuminate\Htpp\Response
     */
    public function getRegencies(Request $request)
    {
        $data = DB::table('regencies')
        ->select('id as value', 'name as label')
        ->when($request->has('province_id') && $request->province_id != null, function ($query) use ($request) {
            $query->where('province_id', $request->province_id);
        })->get();

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET DISTRICTS DATASET
     * @param Request $request
     * @return Illuminate\Htpp\Response
     */
    public function getDistricts(Request $request)
    {
        $data = DB::table('districts')
        ->select('id as value', 'name as label', 'regency_id')
        ->when($request->has('regency_id') && $request->regency_id != null, function ($query) use ($request) {
            $query->where('regency_id', $request->regency_id);
        })->get();
        
        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET VILLAGES DATASET
     * @param Request $request
     * @return Illuminate\Htpp\Response
     */
    public function getVillages(Request $request)
    {
        $data = DB::table('villages')
        ->select('id as value', 'name as label', 'district_id')
        ->when($request->has('district_id') && $request->district_id != null, function ($query) use ($request) {
            $query->where('district_id', $request->district_id);
        })->get();

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET DATA MANAGER IN CURRENT BRANCH
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function getDataManagerCurrentBranch(Request $request)
    {
        if (!$request->has('branch_id') && $request->branch_id == null) {
            return $this->sendBadRequest('Bad Request', 'Branch ID is required');
        }
        
        $managers = DB::table('user_manager_assign as manager')
        ->select('user_manager.id', 'user_manager.name', 'user_manager.email', 'user_manager.profile_picture')
        ->join('user_manager', 'user_manager.id', 'manager.user_manager_id')
        ->where('manager.branch_id', $request->branch_id);

        return $this->sendResponse($managers->get(), 'Data Fetched Successfully');
    }
}
