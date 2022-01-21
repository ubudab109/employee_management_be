<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNoted extends Model
{
    use HasFactory;

    protected $table = 'user_noted';
    protected $fillable = ['user_id','date'];
    protected $appends = ['total_note'];

    public function getTotalNoteAttribute()
    {
        return $this->noteData()->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function noteData()
    {
        return $this->hasMany(UserNotedData::class, 'noted_id', 'id');
    }
}
