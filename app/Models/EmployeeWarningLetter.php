<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWarningLetter extends Model
{
    use HasFactory;

    protected $table = 'employee_warning_letter';
    protected $fillable = [
        'employee_id',
        'date',
        'level',
        'warning_letter',
        'desc',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
