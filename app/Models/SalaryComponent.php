<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'salary_component';
    protected $fillable = ['branch_id', 'name', 'type'];

    public function employeeSalary()
    {
        return $this->hasMany(EmployeeSalary::class, 'salary_component_id', 'id');
    }
}
