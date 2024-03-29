<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOvertime extends Model
{
    use HasFactory;

    protected $table = 'employee_overtime';
    protected $fillable = [
        'employee_id',
        'branch_id',
        'department_id',
        'date',
        'in',
        'out',
        'taken_hour',
        'status',
        'description',
    ];

    protected $appends = ['status_name', 'status_color'];

    public function getStatusColorAttribute()
    {
        return getStatusNameColor($this->status);
    }

    public function getStatusNameAttribute()
    {
        return getStatusNameOvertime($this->status);
    }

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(CompanyDivision::class, 'department_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function files()
    {
        return $this->morphMany(Files::class, 'source');
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
