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
        'type',
        'name',
        'amount'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
