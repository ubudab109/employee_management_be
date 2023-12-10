<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayslipStatus extends Model
{
    use HasFactory;

    protected $table = 'employee_payslip_status';
    protected $fillable = ['branch_id', 'employee_id', 'month', 'years', 'status'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }
}
