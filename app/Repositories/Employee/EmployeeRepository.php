<?php

namespace App\Repositories\Employee;

use App\Models\BankAccount;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeOvertime;
use App\Models\EmployeeReimburshment;
use App\Models\User;
use App\Models\UserDivisionAssign;
use App\Models\UserVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
   * @return Collection
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
      ->join('user_division_assign as department', 'department.user_id', '=', 'users.id')
      ->leftJoin('model_has_roles', function ($leftJoin) {
        $leftJoin->on('department.id', '=', 'model_has_roles.model_id')
          ->where('model_has_roles.model_type', UserDivisionAssign::class);
      })
      ->leftJoin('company_division as division', function ($query) {
        $query->on('division.id', '=', 'department.division_id');
      })
      ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
      ->where('department.branch_id', branchSelected('sanctum:manager')->id)
      // search keyword
      ->when($keyword != null || $keyword != '', function ($query) use ($keyword) {
        $query->where(DB::raw("concat(firstname, ' ', lastname)"), 'LIKE', "%" . $keyword . "%")
          ->orWhere('email', 'like', '%' . $keyword . '%')
          ->orWhere('nip', 'like', '%' . $keyword . '%')
          ->orWhere('phone_number', 'like', '%' . $keyword . '%');
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
      ->when($employeeStatus != null && $employeeStatus != '', function ($query) use ($employeeStatus) {
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
   * @return Collection
   */
  public function getPaginateEmployee($keyword, $department, $jobStatus, $employeeStatus, $show)
  {
    $employee = DB::table('users')->select('users.*', 'department.status', 'department.division_id', 'roles.name as role')
      ->join('division_assign as department', 'department.user_id', '=', 'users.id')
      ->leftJoin('model_has_roles', function ($leftJoin) {
        $leftJoin->on('department.id', '=', 'model_has_roles.model_id')
          ->where('model_has_roles.model_type', UserDivisionAssign::class);
      })
      ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
      ->where('department.branch_id', branchSelected('sanctum:manager')->id)
      // search keyword
      ->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('name', 'like', '%' . $keyword . '%')
          ->orWhere('email', 'like', '%' . $keyword . '%')
          ->orWhere('nip', 'like', '%' . $keyword . '%')
          ->orWhere('phone_number', 'like', '%' . $keyword . '%');
      })
      // filter department
      ->when($department != null, function ($query) use ($department) {
        $query->where('department.division_id', $department);
      })
      // filter job status
      ->when($jobStatus != null, function ($query) use ($jobStatus) {
        $query->where('users.job_status_id', $jobStatus);
      })
      ->when($employeeStatus != null && $employeeStatus != '', function ($query) use ($employeeStatus) {
        $query->where('department.status', $employeeStatus);
      })->paginate($show);

    return $employee;
  }

  /**
   * DETAIL EMPLOYEE
   * 
   * @param int $id - ID from employee or users
   * @param string $param - Detail Type
   * @param array $request - Array of Request
   * @return Collection
   */
  public function detailEmployee($id, $param = null, $request = [])
  {
    switch ($param) {
      case 'employee':
        return $this->model->with('branch')->find($id);
      case 'absent':
        return EmployeeAttendance::where('employee_id', $id)
          ->when(isset($request['date']) && $request['date'] != null, function ($query) use ($request) {
            $query->when(isset($request['date']['month']) && $request['date']['month'] != null, function ($subQuery) use ($request) {
              $subQuery->whereMonth('created_at', $request['date']['month']);
            })
              ->when(isset($request['date']['year']) && $request['date']['year'] != null, function ($subQuery) use ($request) {
                $subQuery->whereYear('created_at', $request['date']['year']);
              });
          })
          ->when(isset($request['status']) && $request['status'] != '', function ($query) use ($request) {
            $query->where('status_clock', $request['status']);
          })
          ->get();
      case 'overtime':
        return EmployeeOvertime::where('employee_id', $id)
          ->when(isset($request['date']) && $request['date'] != null, function ($query) use ($request) {
            $query->when(isset($request['date']['month']) && $request['date']['month'] != null, function ($subQuery) use ($request) {
              $subQuery->whereMonth('date', $request['date']['month']);
            })
              ->when(isset($request['date']['year']) && $request['date']['year'] != null, function ($subQuery) use ($request) {
                $subQuery->whereYear('date', $request['date']['year']);
              });
          })
          ->when(isset($request['status']) && $request['status'] != null, function ($query) use ($request) {
            $query->where('status', $request['status']);
          })
          ->get();
      case 'leave':
        $employeeData = $this->model->find($id);
        return [
          'employee' => [
            'paid_leave_employee' => $employeeData->paid_leave_employee,
            'current_used'        => $employeeData->current_used_pl,
            'total_paid_leave'    => $employeeData->paid_leave_employee - $employeeData->current_used_pl,
          ],
          'leave' => EmployeeLeave::where('employee_id', $id)
            ->with('files:id,files,source_id')
            ->where('type', PAID_LEAVE)
            ->when(isset($request['date']) && $request['date'] != null, function ($query) use ($request) {
              $query->when(isset($request['date']['month']) && $request['date']['month'] != null, function ($subQuery) use ($request) {
                $subQuery->whereMonth('created_at', $request['date']['month']);
              })
                ->when(isset($request['date']['year']) && $request['date']['year'] != null, function ($subQuery) use ($request) {
                  $subQuery->whereYear('created_at', $request['date']['year']);
                });
            })
            ->when(isset($request['status']) && $request['status'] != '', function ($query) use ($request) {
              $query->where('status', $request['status']);
            })
            ->get()
        ];
      case 'permit':
        return EmployeeLeave::where('employee_id', $id)
          ->where('type', PERMIT)
          ->when(isset($request['date']) && $request['date'] != null, function ($query) use ($request) {
            $query->when(isset($request['date']['month']) && $request['date']['month'] != null, function ($subQuery) use ($request) {
              $subQuery->whereMonth('created_at', $request['date']['month']);
            })
              ->when(isset($request['date']['year']) && $request['date']['year'] != null, function ($subQuery) use ($request) {
                $subQuery->whereYear('created_at', $request['date']['year']);
              });
          })
          ->when(isset($request['status']) && $request['status'] != null, function ($query) use ($request) {
            $query->where('status', $request['status']);
          })
          ->get();
      case 'payroll':
        $data = [];
        $data['payment_date'] = DB::table('users')->where('id', $id)->select('id', 'payment_date')->first();
        $data['bank'] = DB::table('bank_account')->where('source_type', User::class)->where('source_id', $id)->first();
        $data['income'] = DB::table('employee_salary')->select('employee_salary.*', 'sc.name')->where('employee_salary.type', SALARY_INCOME)
        ->join('salary_component as sc', 'sc.id', '=', 'employee_salary.salary_component_id')
        ->where('employee_id', $id)->get();
        $data['cuts'] = DB::table('employee_salary')->select('employee_salary.*', 'sc.name')->where('employee_salary.type', SALARY_CUTS)
        ->join('salary_component as sc', 'sc.id', '=', 'employee_salary.salary_component_id')
        ->where('employee_id', $id)->get();
        $data['attendance_cuts'] = DB::table('employee_attendance_cut')->where('employee_id', $id)->get();
        $data['total_salary'] = DB::table('employee_salary')->where('employee_id', $id)->sum('amount');
        return $data;
      case 'reiumbershment':
        return EmployeeReimburshment::where('employee_id', $id)
          ->with('files')
          ->when(isset($request['date']) && $request['date'] != null, function ($query) use ($request) {
            $query->when(isset($request['date']['month']) && $request['date']['month'] != null, function ($subQuery) use ($request) {
              $subQuery->whereMonth('date', $request['date']['month']);
            })
              ->when(isset($request['date']['year']) && $request['date']['year'] != null, function ($subQuery) use ($request) {
                $subQuery->whereYear('date', $request['date']['year']);
              });
          })
          ->when(isset($request['status']) && $request['status'] != null, function ($query) use ($request) {
            $query->where('status', $request['status']);
          })
          ->get();
      case 'warning':
        return DB::table('employee_warning_letter')
          ->where('employee_id', $id)
          ->get();
      default:
        return null;
    }
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
   * It takes an array of data and an id, and then it updates the model with the given id with the
   * given data
   * 
   * @param array data The data to be updated
   * @param integer id The id of the employee you want to update
   * 
   * @return object.
   */
  public function updateEmployee(array $data, $id)
  {
    return $this->model->find($id)->update($data);
  }

  /**
   * Updating assigned branch from spesific user or employee
   * 
   * @param array $data - The data to be updated
   * @param integer $id - The id of the employee you want to update
   * 
   * @return object
   */
  public function updateBranchEmployee(array $data, $id)
  {
    return UserDivisionAssign::where('user_id', $id)->first()->update($data);
  }


  /**
   * It assigns a user to a division
   * 
   * @param User user The user object
   * @param integer $divisionId The id of the division you want to assign the user to.
   * 
   * @return object new instance of the UserDivision model.
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
   * @return object salaryInput method is returning the salary record that was created.
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

  /**
   * UPDATE FINANCE EMPLOYEE
   * INCLUDE: PAYMENT DATE, BANK, SALARY, ATTENDANCE CUT
   * @param array $data
   * @param int $id id employee
   * @return bool
   */
  public function updateFinanceEmployee($data, $type, $id)
  {
    switch($type) {
      case 'payment_date':
        $this->model->find($id)->update(['payment_date' => $data['payment_date']]);
        break;
      case 'bank':
        $employee = $this->model->find($id);
        $employee->bank()->first()->update($data);
        break;
      case 'salary_income':
        $employee = $this->model->find($id);
        foreach($data as $param) {
          $employee->salary()->where('employee_id', $id)->where('salary_component_id', $param['salary_component_id'])->update([
            'amount' => $param['amount']
          ]);
        }
        break;
      case 'salary_cuts':
        $employee = $this->model->find($id);
        foreach ($data as $param) {
          $employee->salary()->where('employee_id', $id)->where('salary_component_id', $param['salary_component_id'])->update([
            'amount' => -$param['amount']
          ]);
        }
        break;
      case 'attendance_cut':
        $employee = $this->model->find($id);
        foreach ($data as $param) {
          $employee->attendanceCut()->where('employee_id', $id)->where('cut_type', $param['cut_type'])->update([
            'amount' => $param['amount'],
            'total'  => $param['total'],
          ]);
        }
        break;
      default: 
        return null;
    }

    return true;
  }
}
