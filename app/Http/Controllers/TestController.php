<?php

namespace App\Http\Controllers;

use App\Jobs\PayslipExportJob;
use App\Models\EmployeeLeave;
use App\Models\ExcelTask;
use App\Models\Payroll;
use App\Repositories\Payroll\PayrollInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PDF;

class TestController extends Controller
{
    public function test()
    {
        $task = ExcelTask::create(['branch_id' => 7, 'manager_id' => 36, 'source_type' => Payroll::class, 'type' => EXCEL_EXPORT]);
        PayslipExportJob::dispatch($task->fresh(), 4, 2023);
        return response()->json(true);
    }
}
