<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotedData extends Model
{
    use HasFactory;

    protected $table = 'user_noted_data';
    protected $fillable = ['noted_id','color','time','note'];
    protected $appends = [
        'format_time',
    ];

    public function getFormatTimeAttribute()
    {
        return Carbon::createFromFormat('H:i:s', $this->time)->format('H:i');    
    }

    public function noteDate()
    {
        return $this->belongsTo(UserNoted::class, 'noted_id', 'id');
    }
}
