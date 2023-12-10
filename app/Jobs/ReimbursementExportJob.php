<?php

namespace App\Jobs;

use App\Models\CompanyBranch;
use App\Models\EmployeeReimburshment;
use App\Models\ExcelTask;
use App\Models\User;
use App\Utilities\Excel\GenerateExcel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

class ReimbursementExportJob extends BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    protected $type;
    protected $branchId;
    protected $task, $date;
    protected $id = [];

    // FOR SPESIFIC EMPOLOYEE
    protected $month, $year, $employeeId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ExcelTask $task, $type, $date, $id, $month, $year, $employeeId)
    {
        parent::__construct();
        $this->task = $task;
        $this->type = $type;
        $this->date = $date;
        $this->id = $id;
        $this->month = $month;
        $this->year = $year;
        $this->employeeId = $employeeId;
        $this->branchId = CompanyBranch::find(branchSelected('sanctum:manager')->id)->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->task->status = EXCEL_PROCESS;
        $this->task->save();
        try {
            switch ($this->type) {
                case 'all':
                    $this->task->download = $this->generateReimbursement();
                    $this->task->message = "Reiumbursement Report Successfully Exported";
                    $this->task->settings = json_encode([
                        'date'        => $this->date,
                        'id'          => $this->id,
                        'month'       => null,
                        'year'        => null,
                        'type'        => 'all',
                        'employee_id' => null,
                        'job_class'   => EmployeeReimburshment::class,
                    ]);
                    $this->task->save();
                    $this->task->status = EXCEL_FINISH;
                    $this->task->save();
                    break;
                case 'employee':
                    $employee = User::find($this->employeeId);
                    $this->task->download = $this->generateReimbursementEmployee();
                    $this->task->message = 'Reiumbursement Report Employee ' . $employee->nip . ' - ' . $employee->firstname . ' ' . $employee->lastname;
                    $this->task->settings = json_encode([
                        'date'        => null,
                        'id'          => null,
                        'month'       => $this->month,
                        'year'        => $this->year,
                        'type'        => 'employee',
                        'employee_id' => $this->employeeId,
                        'job_class'   => EmployeeReimburshment::class,
                    ]);
                    $this->task->save();
                    $this->task->status = EXCEL_FINISH;
                    $this->task->save();
                    break;
                default:
                    throw new \Exception("Invalid type export reimbursement");
            }
        } catch (\Exception $err) {
            $this->task->status = EXCEL_FAILED;
            $this->task->message = $err->getMessage();
            $this->task->save();
        }
    }

    /**
     * GENERATE ALL REIMBURSEMENT EMPLOYEE
     */
    public function generateReimbursement()
    {
        $headers = [
            ['value' => 'ID Reimbursement'],
            ['value' => 'ID Karyawan'],
            ['value' => 'NIP', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Nama', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Posisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Divisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Level'],
            ['value' => 'Status Pekerjaan'],
            ['value' => 'Tipe Claim'],
            ['value' => 'Jumlah'],
            ['value' => 'Status'],
        ];
        if (empty($this->id)) {
            $reimbursements = EmployeeReimburshment::where('branch_id', $this->branchId)
                ->where('date', $this->date)
                ->with(['employee', 'department'])
                ->get();
        } else {
            $reimbursements = EmployeeReimburshment::where('branch_id', $this->branchId)
                ->whereIn('id', $this->id)
                ->where('date', $this->date)
                ->with(['employee', 'department'])
                ->get();
        }

        $data = [];

        foreach ($reimbursements as $reimbursement) {
            $rowData = [];

            for ($i = 0; $i < count($headers); $i++) {
                $rowData = [
                    $reimbursement->id,
                    $reimbursement->employee->id,
                    $reimbursement->employee->nip,
                    $reimbursement->employee->firstname . ' ' . $reimbursement->employee->lastname,
                    ucwords($reimbursement->employee->job_position),
                    $reimbursement->employee->division_name,
                    $reimbursement->employee->job_level,
                    $reimbursement->employee->job_status_name,
                    $reimbursement->claim_type,
                    rupiah($reimbursement->amount),
                    $reimbursement->getStatusNameAttribute(),
                ];
            }
            $data[] = $rowData;
        }

        $fileName = 'Reimbursement' . $this->date;
        $dir = 'export/reimbursement/' . $fileName . '.xlsx';
        Excel::store(
            new GenerateExcel('Reimbursement Report', [$headers], $data, ['header_style' =>
            ['background' => 'F4FF90', 'bold' => true, 'auto_size' => true], 'column_style' =>
            ['A' => ['protection' => false]], 'body_style' => ['protection' => false], 'freeze_pane' => 'B2']),
            $dir,
            'public'
        );
        return URL::to(Storage::url($dir));
    }

    /**
     * EXPORT REIMBURSEMENT FROM SPESIFIC EMPLOYEE
     */
    public function generateReimbursementEmployee()
    {
        $headers = [
            ['value' => 'ID Reimbursement'],
            ['value' => 'ID Karyawan'],
            ['value' => 'NIP', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Nama', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Posisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Divisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Level'],
            ['value' => 'Status Pekerjaan'],
            ['value' => 'Tipe Claim'],
            ['value' => 'Jumlah'],
            ['value' => 'Status'],
        ];

        $dates = [
            'month' => $this->month,
            'year'  => $this->year,
        ];

        $employee = User::find($this->employeeId);

        $reimbursements = EmployeeReimburshment::where('branch_id', $this->branchId)
            ->where('employee_id', $this->employeeId)
            ->whereMonth('date', $dates['month'])
            ->whereYear('date', $dates['year'])
            ->with(['employee', 'department'])
            ->get();

        $data = [];

        foreach ($reimbursements as $reimbursement) {
            $rowData = [];

            for ($i = 0; $i < count($headers); $i++) {
                $rowData = [
                    $reimbursement->id,
                    $reimbursement->employee->id,
                    $reimbursement->employee->nip,
                    $reimbursement->employee->firstname . ' ' . $reimbursement->employee->lastname,
                    ucwords($reimbursement->employee->job_position),
                    $reimbursement->employee->division_name,
                    $reimbursement->employee->job_level,
                    $reimbursement->employee->job_status_name,
                    $reimbursement->claim_type,
                    rupiah($reimbursement->amount),
                    $reimbursement->getStatusNameAttribute(),
                ];
            }
            $data[] = $rowData;
        }

        $fileName = 'Reimbursement-' . $employee->nip . '-' . getMonthName($this->month) . '-' . $this->year;
        $dir = 'export/reimbursement/' . $employee->nip . '/' . $fileName . '.xlsx';
        Excel::store(
            new GenerateExcel('Reimbursement '. $employee->nip .' Report', [$headers], $data, ['header_style' =>
            ['background' => 'F4FF90', 'bold' => true, 'auto_size' => true], 'column_style' =>
            ['A' => ['protection' => false]], 'body_style' => ['protection' => false], 'freeze_pane' => 'B2']),
            $dir,
            'public'
        );
        return URL::to(Storage::url($dir));
    }
}
