<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PayrollGenerateProcess
 *
 * @property int $id
 * @property int $branch_id
 * @property int $month
 * @property int $years
 * @property string $years
 * @property bool $created_at
 * @property bool $updated_at
 */
class PayrollGenerateProcess extends Model
{
    use HasFactory;

    protected $table = 'payroll_generate_process';
    protected $fillable = [
        'message',
        'branch_id',
        'month',
        'years',
        'status'
    ];

    public function branch()
    {
        return $this->belongsTo(CompanyBranch::class, 'branch_id', 'id');
    }
}
