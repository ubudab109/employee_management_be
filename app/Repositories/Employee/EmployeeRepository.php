<?php

namespace App\Repositories\Employee;

use App\Models\BankAccount;
use App\Models\User;
use App\Models\UserDivisionAssign;
use App\Models\UserVerification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class EmployeeRepository implements EmployeeInterface
{
    /**
    * @var ModelName
    */
    protected $model, $verification, $bankAccount;

    public function __construct(User $model, UserVerification $userVerification, BankAccount $bankAccount)
    {
      $this->model = $model;
      $this->verification = $userVerification;
      $this->bankAccount = $bankAccount;
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
      $employee = $this->model
      ->select(
        'users.*',
        'department.status',
        'department.division_id',
        'roles.name as role',
        'division.division_name',
      )
      ->join('user_division_assign as department','department.user_id','=','users.id')
      ->leftJoin('model_has_roles', function ($leftJoin) {
        $leftJoin->on('department.id','=','model_has_roles.model_id')
        ->where('model_has_roles.model_type',UserDivisionAssign::class);
      })
      ->leftJoin('company_division as division', function ($query) {
        $query->on('division.id','=','department.division_id');
      })
      ->leftJoin('roles','model_has_roles.role_id','=','roles.id')
      ->where('department.branch_id',branchSelected('sanctum:manager')->id)
      // search keyword
      ->when($keyword != null || $keyword != '', function ($query) use ($keyword) {
        $query->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', "%".$keyword."%")
        ->orWhere('email','like','%'.$keyword.'%')
        ->orWhere('nip','like','%'.$keyword.'%')
        ->orWhere('phone_number','like','%'.$keyword.'%');
      })
      // filter department
      ->when($department != null && $department != 0, function ($query) use ($department) {
        $query->where('department.division_id', $department);
      })
      // filter job status
      ->when($jobStatus != null && $jobStatus != 'all', function ($query) use ($jobStatus) {
        $query->where('users.job_status', $jobStatus);
      })
      // filter user status
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
      ->where('department.branch_id',branchSelected('sanctum:manager')->id)
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

    /**
     * It creates an employee and returns the employee
     * 
     * @param array data - data to input
     * 
     * @return object employee object
     */
    public function createEmployee(array $data)
    {
        $employeeData = $data;
        if (isset($data['profile_picture'])) {
          $employeeData['profile_picture'] = $data['profile_picture'];
        }
        if (isset($data['salary_settings'])) {
          $employeeData['salary_settings'] = json_encode($data['salary_settings']);
        }
        $employee = $this->model->create($employeeData);
        return $employee;
    }

    /**
     * It assigns a user to a division
     * 
     * @param User user The user object
     * @param divisionId The id of the division you want to assign the user to.
     * 
     * @return A new instance of the UserDivision model.
     */
    public function assignEmployeeToDepartment(User $user, $divisionId)
    {
      return $user->userDivision()->create([
        'branch_id'   => branchSelected('sanctum:manager')->id,
        'division_id' => $divisionId,
      ]);
    }

    /**
     * Create a new salary record for the given user.
     * 
     * @param User user The user model instance
     * @param array data
     * 
     * @return The salaryInput method is returning the salary record that was created.
     */
    public function salaryInput(User $user, array $data)
    {
        return $user->salary()->create($data);
    }

    /**
     * Create a new attendance cut record for the given user.
     * 
     * @param array data 
     * 
     * @return object
     */
    public function attendanceCutInput(User $user, array $data)
    {
        return $user->attendanceCut()->create($data);
    }

    /**
     * It creates a new bank account for a user
     * 
     * @param array data This is the array of data that you want to insert into the database.
     * 
     * @return object
     */
    public function createBankAccount(array $data)
    {
      return $this->bankAccount->create($data);
    }

    /**
     * It deletes the employee with the given id
     * 
     * @param array id The id of the employee to be deleted
     * 
     * @return array return value is the number of rows affected by the delete query.
     */
    public function deleteEmployee(array $id)
    {
      return $this->model->whereIn('id', $id)->delete();
    }
    

}
