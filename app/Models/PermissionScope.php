<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionScope extends Model
{
    use HasFactory;

    protected $table = 'permission_scope';
    protected $fillable = ['name'];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'scope_id', 'id');
    }
}
