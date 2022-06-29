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

class UserManager extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $table = 'user_manager';
    protected $guard_name = 'sanctum:manager';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip',
        'gender',
        'profile_picture',
        'email_verified_at',
        'phone_number',
        'password',
        'invited_status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_picture'
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
        'avatar',
        'role',
        'is_superadmin'
    ];


    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function getIsSuperadminAttribute()
    {
        if ($this->branch()->first() == null) return true;
        return false;
    }

    public function getAvatarAttribute()
    {   

        if ($this->profile_picture === NULL) {
            $avatar = new Avatar();
            $image = $avatar->create(ucfirst($this->name[0]))->setBackground('#F79E1B')->setDimension(400, 400)->setFontSize(170)->toBase64();
        } else {
            $image = $this->profile_picture;
        }
        return $image;
    }

    public function getRoleAttribute()
    {   
        if ($this->branch()->exists()) {
            return ucfirst($this->branchAssign()->first()->pivot->roles()->first()->name);
        } else {
            return ucfirst($this->roles()->first()->name);
        }

    }

    public function verification()
    {
        return $this->morphMany(UserVerification::class, 'model');
    }

    public function branch()
    {
        return $this->hasOne(UserManagerAssign::class, 'user_manager_id', 'id');
    }

    public function branchAssign()
    {
        return $this->belongsToMany(CompanyBranch::class, 'user_manager_assign', 'user_manager_id', 'branch_id')->using(UserManagerAssign::class)
        ->withPivot('id', 'user_manager_id', 'branch_id','status',);
    }

}
