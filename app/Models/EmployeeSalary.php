<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'employee_salary';
    protected $fillable = [
        'employee_id',
        'salary_component_id',
        'type',
        'amount',
        'is_temporary',
        'setting',
    ];
    
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id', 'id');
    }
}
