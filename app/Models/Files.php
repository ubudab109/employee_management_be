<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    protected $table = 'files';
    protected $fillable = [
        'files',
        'type',
        'label',
        'source_type',
        'source_id',
    ];

    public function source()
    {
        return $this->morphTo('source');
    }
}
