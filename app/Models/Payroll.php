<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payroll';
    protected $fillable = [
        'branch_id',
        'employee_id',
        'department_id',
        'payroll_code',
        'salary_name',
        'amount',
        'type',
        'month',
        'years',
        'generate_date',
        'status',
    ];
    protected $appends = ['currencyAmount', 'isDisabled', 'currentAmount'];

    public function getCurrencyAmountAttribute()
    {
        return rupiah($this->amount);
    }

    public function getIsDisabledAttribute()
    {
        return true;
    }

    public function getCurrentAmountAttribute()
    {
        return $this->amount;
    }
    
    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(CompanyDivision::class, 'department_id', 'id');
    }

    public function notificationManagers()
    {
        return $this->morphMany(NotificationManager::class, 'model');
    }

    public function notificationEmployees()
    {
        return $this->morphMany(NotificationEmployee::class, 'model');
    }
}
