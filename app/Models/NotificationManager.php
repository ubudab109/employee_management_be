<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationManager extends Model
{
    use HasFactory;

    protected $table = 'notification_manager';
    protected $fillable = [
        'branch_id',
        'employee_id',
        'user_manager_id',
        'model_type',
        'model_id',
        'fe_url',
        'title',
        'message',
        'is_read'
    ];

    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(UserManager::class, 'user_manager_id', 'id');
    }

    public function model()
    {
        return $this->morphTo('model');
    }
}
