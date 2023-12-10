<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PTKP extends Model
{
    use HasFactory;

    protected $table = 'ptkp';
    protected $fillable = ['criteria', 'status', 'ptkp_amount'];

    public function employee()
    {
        return $this->hasOne(User::class, 'ptkp_id', 'id');
    }
}
