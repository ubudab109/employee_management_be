<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'auth:sanctum';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'nip',
        'gender',
        'profile_picture',
        'phone_number',
        'mobile_phone',
        'religion',
        'pob',
        'dob',
        'citizent_address',
        'resident_address',
        'is_address_same',
        'postal_code',
        'join_date',
        'end_date',
        'job_status',
        'job_level',
        'job_position',
        'marital_status',
        'blood_type',
        'identity_type',
        'identity_number',
        'identity_expired',
        'email_verified_at',
        'password',
        'payment_date',
        'salary_settings',
        'paid_leave_years',
        'paid_leave_employee',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'salary_settings'   => 'object',
    ];

    protected $appends = [
        'avatar', 'division_name', 'total_salary', 'total_income', 
        'total_cuts', 'status', 'status_badge', 'status_name', 'job_status_name',
        'date_of_birth', 'identity_type_name', 'date_human_diff', 'current_used_pl'
    ];

    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function getCurrentUsedPlAttribute()
    {
        $currentUsedPerYear = $this->leave()->where('type', PAID_LEAVE)->whereYear('created_at', date('Y'))->where('status', APPROVED)->count();
        return $currentUsedPerYear;
    }

    public function getDateHumanDiffAttribute()
    {
        return [
            'join_date' => date('d F Y', strtotime($this->join_date)),
            'end_date'  => $this->end_date !== null ? date('d F Y', strtotime($this->end_date)) : null,
        ];
    }

    public function getIdentityTypeNameAttribute()
    {
        return getIdentityTypeName($this->identity_type);
    }

    public function getDateOfBirthAttribute()
    {
        return date('d F Y', strtotime($this->dob));    
    }

    public function getJobStatusNameAttribute()
    {
        return getJobStatusName($this->job_status);
    }

    public function getStatusNameAttribute()
    {
        if ($this->getStatusAttribute() == EMPLOYEE_ACTIVE) {
            $name = 'Active';
        } else if ($this->getStatusAttribute() === EMPLOYEE_INACTIVE) {
            $name = 'Not Active';
        } else {
            $name = 'Pending';
        }

        return $name;
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->getStatusAttribute() == EMPLOYEE_ACTIVE) {
            $badge = [
                'badge' => '#BBFFCC',
                'color' => '#008836',
            ];
        } else if ($this->getStatusAttribute() === EMPLOYEE_INACTIVE) {
            $badge = [
                'badge' => '#FCE8E8',
                'color' => '#FF1111',
            ];
        } else {
            $badge = [
                'badge' => '#FFEBD7',
                'color' => '#D88430',
            ];
        }

        return $badge;
    }

    public function getDivisionNameAttribute()
    {
        if ($this->division()->first()) {
            return $this->division()->first()->division_name;
        }

        return null;
    }


    public function getTotalSalaryAttribute()
    {
        $total = [];
        foreach ($this->salary()->get() as $salary) {
            if (is_null($salary->setting)) {
                array_push($total, $salary->amount);
            }
        }
        return array_sum($total);
    }

    public function getTotalIncomeAttribute()
    {
        return $this->salary()->where('type', SALARY_INCOME)->sum('amount');
    }

    public function getTotalCutsAttribute()
    {
        return $this->salary()->where('type', SALARY_CUTS)->sum('amount');
    }

    public function getStatusAttribute()
    {
        return $this->userDivision()->first()->status;
    }

    public function getAvatarAttribute()
    {

        if ($this->profile_picture === NULL) {
            $avatar = new Avatar();
            $image = $avatar->create($this->firstname[0])->setBackground('#F79E1B')->toBase64();
        } else {
            $image = $this->profile_picture;
        }
        return $image;
    }

    public function jobStatus()
    {
        return $this->belongsTo(CompanyJobStatus::class, 'job_status_id', 'id');
    }

    public function division()
    {
        return $this->belongsToMany(CompanyDivision::class, 'user_division_assign', 'user_id', 'division_id')->using(UserDivisionAssign::class)->withPivot('id', 'user_id', 'division_id', 'status', 'employment_type');
    }

    public function branch()
    {
        return $this->hasOne(UserDivisionAssign::class, 'user_id', 'id');
    }

    public function branchAssign()
    {
        return $this->belongsToMany(CompanyBranch::class, 'user_division_assign', 'user_id', 'branch_id')->using(UserDivisionAssign::class)
            ->withPivot('id', 'user_id', 'division_id', 'branch_id', 'status', 'employment_type');
    }

    public function userDivision()
    {
        return $this->hasOne(UserDivisionAssign::class, 'user_id', 'id');
    }

    public function noted()
    {
        return $this->hasMany(UserNoted::class, 'user_id', 'id');
    }

    public function verification()
    {
        return $this->morphMany(UserVerification::class, 'model');
    }

    public function attendance()
    {
        return $this->hasMany(EmployeeAttendance::class, 'employee_id', 'id');
    }

    public function salary()
    {
        return $this->hasMany(EmployeeSalary::class, 'employee_id', 'id');
    }

    public function income()
    {
        return $this->salary()->where('type', SALARY_INCOME)->get();
    }

    public function cuts()
    {
        return $this->salary()->where('type', SALARY_INCOME)->get();
    }

    public function attendanceCut()
    {
        return $this->hasMany(EmployeeAttendanceCut::class, 'employee_id', 'id');
    }

    public function bank()
    {
        return $this->morphMany(BankAccount::class, 'source');
    }

    public function overtime()
    {
        return $this->hasMany(EmployeeOvertime::class, 'employee_id', 'id');
    }

    public function reimburshment()
    {
        return $this->hasMany(EmployeeReimburshment::class, 'employee_id', 'id');
    }

    public function leave()
    {
        return $this->hasMany(EmployeeLeave::class, 'employee_id', 'id');
    }

    public function warningLetter()
    {
        return $this->hasMany(EmployeeWarningLetter::class, 'employee_id', 'id');
    }

    public function paySlip()
    {
        return $this->hasMany(Payroll::class, 'employee_id', 'id');
    }
}
