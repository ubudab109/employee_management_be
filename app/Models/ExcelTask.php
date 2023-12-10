<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelTask extends Model
{
    use HasFactory;

    protected $table = 'excel_tasks';
    protected $fillable = ['branch_id', 'manager_id', 'source_type', 'source_id', 'type', 'download', 'message', 'settings', 'status'];

    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(UserManager::class, 'manager_id', 'id');
    }

    public function source()
    {
        return $this->morphTo('source');
    }
}
