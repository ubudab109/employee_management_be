<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravolt\Avatar\Avatar;

class CompanyJobStatus extends Model
{
    use HasFactory;

    protected $table = 'company_job_status';
    protected $fillable = [
        'name',
        'style_color',
    ];

    protected $appends = ['icon'];

    public function getIconAttribute()
    {
        $avatar = new Avatar();
        $icon = $avatar->create(ucfirst($this->name))->setBackground($this->style_color)->setDimension(400,400)->setFontSize(170)->toBase64();
        return $icon;
    }
    
    public function employee()
    {
        return $this->hasMany(User::class, 'job_status_id', 'id');
    }


}
