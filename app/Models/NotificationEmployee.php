<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEmployee extends Model
{
    use HasFactory;

    protected $table = 'notification_employee';
    protected $fillable = [
        'branch_id',
        'employee_id',
        'model',
        'title',
        'message',
        'is_read',
        'icon',
    ];

    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function model()
    {
        return $this->morphTo('model');
    }
}
