<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
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
        'name',
        'email',
        'nip',
        'profile_picture',
        'phone_number',
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

    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function division()
    {
        return $this->belongsToMany(CompanyDivision::class, 'user_division_assign', 'user_id', 'division_id')->using(UserDivisionAssign::class)->withPivot('id', 'user_id', 'division_id', 'status', 'employment_type');
    }

    public function userDivision()
    {
        return $this->belongsTo(UserDivisionAssign::class, 'user_id', 'id');
    }

    public function noted()
    {
        return $this->hasMany(UserNoted::class, 'user_id', 'id');
    }
}
