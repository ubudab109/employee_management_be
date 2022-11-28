<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;
    
    protected $table = 'bank_account';
    protected $fillable = [
        'source_type',
        'source_id',
        'bank_name',
        'account_number',
        'account_holder_name',
    ];

    public function source()
    {
        return $this->morphTo('source');
    }

}
