<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReimburshment extends Model
{
    use HasFactory;

    protected $table = 'employee_reimburshment';
    protected $fillable = [
        'employee_id',
        'date',
        'claim_type',
        'amount',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
