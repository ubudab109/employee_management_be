<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollStatus extends Model
{
    use HasFactory;

    protected $table = 'payroll_status';
    protected $fillable = ['branch_id', 'month', 'years', 'status'];
    
    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }
}
