<?php

namespace App\Repositories\Employee;

use App\Models\User;
use App\Models\UserDivisionAssign;
use App\Models\UserVerification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class EmployeeRepository
{
    /**
    * @var ModelName
    */
    protected $model, $verification;

    public function __construct(User $model, UserVerification $userVerification)
    {
      $this->model = $model;
      $this->verification = $userVerification;
    }

    /**
     * GET EMPLOYEE DATA WITHOUT PAGINATE
     * 
     * @param string $keyword — searching by keyword like name, email, nip or phone number
     * @param int $department — filter by departement or division id
     * @param int $jobStatus — filter by job status like full time, freelance or etc. According to company job status
     * @param string $employeeStatus — filter by status employee like active ('1'), inactive ('0'), pending ('2')
     * @return Array
     */
    public function getAllEmployee($keyword, $department, $jobStatus, $employeeStatus)
    {
      $employee = DB::table('users')->select('users.*','department.status','department.division_id','roles.name as role')
      ->join('division_assign as department','department.user_id','=','users.id')
      ->leftJoin('model_has_roles', function ($leftJoin) {
        $leftJoin->on('department.id','=','model_has_roles.model_id')
        ->where('model_has_roles.model_type',UserDivisionAssign::class);
      })
      ->leftJoin('roles','model_has_roles.role_id','=','roles.id')
      // search keyword
      ->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('name','like','%'.$keyword.'%')
        ->orWhere('email','like','%'.$keyword.'%')
        ->orWhere('nip','like','%'.$keyword.'%')
        ->orWhere('phone_number','like','%'.$keyword.'%');
      })
      // filter department
      ->when($department != null, function ($query) use ($department) {
        $query->where('department.division_id', $department);
      })
      // filter job status
      ->when($jobStatus != null, function ($query) use ($jobStatus) {
        $query->where('users.job_status_id', $jobStatus);
      })
      ->when($employeeStatus != null && $employeeStatus != '', function ($query) use ($employeeStatus){
        $query->where('department.status', $employeeStatus);
      })->get();

      return $employee;
    }

    /**
     * GET EMPLOYEE DATA WITH PAGINATE
     * 
     * @param string $keyword — searching by keyword like name, email, nip or phone number
     * @param int $department — filter by departement or division id
     * @param int $jobStatus — filter by job status like full time, freelance or etc. According to company job status
     * @param string $employeeStatus — filter by status employee like active ('1'), inactive ('0'), pending ('2')
     * @param int $show — total data per page
     * @return Array
     */
    public function getPaginateEmployee($keyword, $department, $jobStatus, $employeeStatus, $show)
    {
      $employee = DB::table('users')->select('users.*','department.status','department.division_id','roles.name as role')
      ->join('division_assign as department','department.user_id','=','users.id')
      ->leftJoin('model_has_roles', function ($leftJoin) {
        $leftJoin->on('department.id','=','model_has_roles.model_id')
        ->where('model_has_roles.model_type',UserDivisionAssign::class);
      })
      ->leftJoin('roles','model_has_roles.role_id','=','roles.id')
      // search keyword
      ->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('name','like','%'.$keyword.'%')
        ->orWhere('email','like','%'.$keyword.'%')
        ->orWhere('nip','like','%'.$keyword.'%')
        ->orWhere('phone_number','like','%'.$keyword.'%');
      })
      // filter department
      ->when($department != null, function ($query) use ($department) {
        $query->where('department.division_id', $department);
      })
      // filter job status
      ->when($jobStatus != null, function ($query) use ($jobStatus) {
        $query->where('users.job_status_id', $jobStatus);
      })
      ->when($employeeStatus != null && $employeeStatus != '', function ($query) use ($employeeStatus){
        $query->where('department.status', $employeeStatus);
      })->paginate($show);

      return $employee;
    }

    /**
     * DETAIL EMPLOYEE
     * 
     * @param int $id - ID from employee or users
     * @return \App\Models\User
     */
    public function detailEmployee($id)
    {
      $employee = $this->model->with('attendance')->with('jobStatus')->findOrFail($id);
      return $employee;
    }

    /**
     * VERIFY EMAIL USER
     * 
     * @param int $id - ID from employee or users
     * @return \App\Models\User
     */
    public function verifyEmployee($id)
    {
      return $this->model->findOrFail($id)->update([
        'email_verified_at' => Date::now(),
      ]);
    }

    

}