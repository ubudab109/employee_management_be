<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceLocation extends Model
{
    use HasFactory;

    protected $table = 'employee_attendance_location';
    protected $fillable = [
        'employee_attendance_id',
        'latitude',
        'langitude',
        'location',
        'clock_type',
    ];

    public function employeeAttendance()
    {
        return $this->belongsTo(EmployeeAttendance::class, 'employee_attendance_id', 'id');
    }
}
