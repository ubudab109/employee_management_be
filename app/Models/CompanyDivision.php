<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDivision extends Model
{
    use HasFactory;

    protected $table = 'company_division';
    protected $fillable = ['division_code','division_name'];

    public function divisionAssign()
    {
        return $this->hasMany(UserDivisionAssign::class, 'division_id', 'id');
    }

    public function userDivisionAssign()
    {
        return $this->belongsToMany(User::class, 'user_division_assign', 'division_id', 'user_id')->using(UserDivisionAssign::class)->withPivot('id', 'user_id', 'division_id', 'status');
    }
}
