<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;

class UserDivisionAssign extends Pivot
{
    use HasFactory, HasRoles;

    protected $table = 'user_division_assign';
    protected $fillable = ['uuid','user_id','division_id','status','employment_type'];
    protected $guard_name ='auth:sanctum';


    protected static function boot()
    {
        parent::boot(); //

        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(CompanyDivision::class, 'division_id', 'id');
    }
    
}
