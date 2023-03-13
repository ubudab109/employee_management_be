<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBranch extends Model
{
    use HasFactory;

    protected $table = 'company_branch';
    protected $fillable = [
        'branch_name',
        'branch_code',
        'branch_order',
        'is_centered',
        'province_id',
        'regency_id',
        'district_id',
        'villages_id',
        'address',
        'status',
        'latitude',
        'longitude',
        'attendance_radius',
        'work_type',
        'is_radius_active',
    ];

    public function provincies()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function regencies()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function villages()
    {
        return $this->belongsTo(Village::class, 'villages_id', 'id');
    }

    public function employeeSalary()
    {
        return $this->hasMany(EmployeeSalary::class, 'branch_id', 'id');
    }

    public function payslip()
    {
        return $this->hasMany(Payroll::class, 'branch_id', 'id');
    }
}
