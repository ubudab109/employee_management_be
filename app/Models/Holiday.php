<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';
    protected $fillable = ['years', 'month', 'month_name', 'data', 'type'];
    protected $casts = [
        'data' => 'json',
    ];
}
