<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySchedule extends Model
{
    use HasFactory;

    protected $table = 'company_schedule';
    protected $fillable = ['branch_id', 'code', 'is_default', 'name', 'clock_in', 'clock_out', 'lat', 'long', 'map_url'];

    public function branch()
    {
        return $this->belongsTo(CompanyDivision::class, 'branch_id', 'id');
    }
}
