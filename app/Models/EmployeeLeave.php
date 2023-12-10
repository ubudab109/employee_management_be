<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    use HasFactory;

    protected $table = 'employee_leave';
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_id',
        'desc',
        'start_date',
        'end_date',
        'taken',
        'status',
        'type',
    ];
    protected $appends = ['badge_color', 'status_name'];

    public function getBadgeColorAttribute()
    {
        return getStatusNameColor($this->status);
    }

    public function getStatusNameAttribute()
    {
        return getLeaveStatusName($this->status);
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
