<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    use HasFactory;

    protected $table = 'user_verification';
    protected $fillable = [
        'model_type',
        'model_id',
        'code',
        'verification_type',
        'expired_at',
    ];

    public function model()
    {
        return $this->morphTo('model');
    }
}
