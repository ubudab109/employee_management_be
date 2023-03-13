<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReimburshment extends Model
{
    use HasFactory;

    protected $table = 'employee_reimburshment';
    protected $fillable = [
        'branch_id',
        'employee_id',
        'department_id',
        'date',
        'claim_type',
        'amount',
        'status',
    ];
    protected $appends = ['status_name', 'status_color'];

    public function getStatusNameAttribute()
    {
        return getStatusNameAttribute($this->status);    
    }

    public function getStatusColorAttribute()
    {
        return getStatusNameColor($this->status);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(CompanyDivision::class, 'department_id', 'id');
    }

    public function files()
    {
        return $this->morphMany(Files::class, 'source');
    }
}
