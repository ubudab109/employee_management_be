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
        'postal_code',
        'join_date',
        'end_date',
        'job_status',
        'job_level',
        'marital_status',
        'blood_type',
        'identity_type',
        'identity_number',
        'email_verified_at',
        'password',
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
    ];

    protected $appends = [
        'avatar', 'roles_name', 'division_name'
    ];

    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function getDivisionNameAttribute()
    {
        return $this->division()->first()->division_name;
    }


    public function getRolesNameAttribute()
    {
        return ucfirst($this->branchAssign()->first()->pivot->roles()->first()->name);
    }

    public function getAvatarAttribute()
    {   

        if ($this->profile_picture === NULL) {
            $avatar = new Avatar();
            $image = $avatar->create($this->name[0])->setBackground('#F79E1B')->toBase64();
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
        ->withPivot('id', 'user_id', 'division_id', 'branch_id','status', 'employment_type');
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
        return $this->hasMany(EmployeeAttendance::class, 'employee_id','id');
    }

    
}
