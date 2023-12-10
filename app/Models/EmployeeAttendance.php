<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance';
    protected $fillable = [
        'employee_id',
        'work_places',
        'status_clock',
        'branch_id',
        'clock_in',
        'clock_out',
        'date',
    ];

    protected $appends = [
        'workplace_name',
        'workplace_badge',
        'workplace_color',
        'absent_status',
        'absent_badge',
        'absent_color'
    ];

    public function getWorkplaceNameAttribute()
    {
        return getWorkPlaceName($this->work_places);
    }

    public function getWorkplaceBadgeAttribute()
    {
        return badgeWorkPlaces($this->work_places);
    }

    public function getWorkplaceColorAttribute()
    {
        return textColorWorkSpaces($this->work_places);
    }

    public function getAbsentStatusAttribute()
    {
        return getStatusAbsent($this->status_clock);
    }

    public function getAbsentBadgeAttribute()
    {
        return badgeStatusAbsen($this->status_clock);
    }

    public function getAbsentColorAttribute()
    {
        return textColorStatusAbsent($this->status_clock);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function files()
    {
        return $this->morphOne(Files::class, 'source');
    }

    public function attendanceLocation()
    {
        return $this->hasMany(EmployeeAttendanceLocation::class, 'employee_attendance_id', 'id');
    }

    public function clockIn()
    {
        return $this->attendanceLocation()->where('clock_type','0')->with('files:id,files')->first();
    }

    public function clockOut()
    {
        return $this->attendanceLocation()->where('clock_type','0')->with('files:id,files')->first();
    }

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
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
