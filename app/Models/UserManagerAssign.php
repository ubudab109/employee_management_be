<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class UserManagerAssign extends Pivot
{
    use HasFactory, HasRoles;
    protected $table = 'user_manager_assign';
    protected $fillable = [
        'uuid',
        'user_manager_id',
        'branch_id',
        'status'
    ];
    protected $guard_name = 'sanctum:manager';
    protected $primaryKey = 'id';
    protected $appends = ['role', 'company_name'];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }
    
    public function getRoleAttribute()
    {
        return $this->roles()->first();
    }

    public function getCompanyNameAttribute()
    {
        return settings('company_name');
    }

    public function userManager()
    {
        return $this->belongsTo(UserManager::class, 'user_manager_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
    }
}
