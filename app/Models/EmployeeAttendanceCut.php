<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceCut extends Model
{
    use HasFactory;
    protected $table = 'employee_attendance_cut';
    protected $fillable = [
        'employee_id',
        'cut_type',
        'total',
        'amount'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
