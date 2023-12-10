<?php

namespace App\Jobs;

use App\Models\CompanyBranch;
use App\Models\ExcelTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Payroll;
use App\Models\User;
use App\Utilities\Excel\GenerateExcel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

class PayslipExportJob extends BaseJob implements ShouldQueue
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

    protected $task, $month, $year, $branchId;
    protected $employeeId = [];

    /**
     * Create a new job instance.
     *
     * @param ExportExcelTask $task
     * @param int $month - Spesific month of payslip
     * @param int $year - Spesific year of payslip
     * @param int $branchId - Current branch company
     */
    public function __construct(ExcelTask $task, $month, $year, $employeeId)
    {
        parent::__construct();
        $this->task = $task;
        $this->month = $month;
        $this->year = $year;
        $this->branchId = CompanyBranch::find(branchSelected('sanctum:manager')->id);
        $this->employeeId = $employeeId;
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
            $this->task->download = $this->generatePayrollDataExcel();
            $this->task->message = 'Payslip exported successfully';
            $this->task->save();
            $this->task->status = EXCEL_FINISH;
            $this->task->settings = json_encode([
                'month'       => $this->month,
                'year'        => $this->year,
                'employee_id' => $this->employeeId,
                'job_class'   => PayslipExportJob::class,
            ]);
            $this->task->save();
        } catch (\Exception $err) {
            $this->task->status = EXCEL_FAILED;
            $this->task->message = $err->getMessage();
            $this->task->save();
            throw $err;
        }
    }

    /**
     * The function retrieves unique salary names from a payroll table for a specific month and year
     * and returns them in an array format.
     * 
     * @return array array of unique salary names from the payroll table for a specific month and year.
     * Each salary name is represented as an associative array with a key of 'value'.
     */
    private function groupSalaryName()
    {
        $payroll = Payroll::where('month', $this->month)->where('years', $this->year)
            ->orderBy('type')
            ->pluck('salary_name')->toArray();
        $collect = collect($payroll)->values()->all();
        $unique = array_unique($collect);
        $datas = array_values($unique);
        $resData = [];
        foreach ($datas as $data) {
            $resData[] = ['value' => $data];
        }
        return $resData;
    }

    /**
     * This function retrieves the salary amount for a specific employee and salary name for a given
     * month and year.
     * 
     * @param integer $employeeId The ID of the employee whose salary is being retrieved.
     * @param string #salaryName The name of the salary for which the function is trying to retrieve the
     * amount.
     * 
     * @return string salary amount for a specific salary name and employee ID for a given month and year.
     * If the salary detail is not found, it returns 0. If the salary type is a cut, it returns a
     * negative value, otherwise, it returns the salary amount with rupiah format.
     */
    private function getSalaryBySalarName($employeeId, $salaryName)
    {
        $salaryDetail = DB::table('payroll')
            ->where('employee_id', $employeeId)
            ->where('salary_name', $salaryName)
            ->where('month', $this->month)
            ->where('years', $this->year)
            ->first();

        if (!$salaryDetail) {
            return rupiah(0);
        }

        if ($salaryDetail->type == SALARY_CUTS) {
            $amount = -$salaryDetail->amount;
        } else {
            $amount = $salaryDetail->amount;
        }
        return rupiah($amount);
    }

    /**
     * Get total nett salary employee
     * 
     * @param int $employeeId - Id of employee
     * @return string total nett salary with rupiah format
     */
    private function getNettSalary($employeeId)
    {
        $totalPayslip = DB::table('payroll')
            ->where('employee_id', $employeeId)
            ->where('month', $this->month)
            ->where('years', $this->year)
            ->sum('amount');

        return rupiah($totalPayslip);
    }

    /**
     * This function generates an Excel file containing payroll data for employees based on their
     * salary and other information.
     * 
     * @return string URL to the generated Excel file.
     */
    public function generatePayrollDataExcel()
    {
        $headers = [
            ['value' => 'ID Karyawan'],
            ['value' => 'NIP', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Nama', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Posisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Divisi', 'style' => ['width' => 50, 'wrap_text' => true]],
            ['value' => 'Level'],
            ['value' => 'Status Pekerjaan'],
        ];

        $headers2 = $this->groupSalaryName();

        $headers3 = [
            ['value' => 'Total Gaji (NETT)']
        ];

        $date = [
            'month' => $this->month,
            'years' => $this->year,
        ];

        $data = User::with(['payslipStatus' => function ($query) use ($date) {
            $query->where('month', $date['month'])
                ->where('years', $date['years']);
        }])
            ->whereHas('branch', function ($query) {
                $query->where('branch_id', $this->branchId->id);
            })
            ->whereHas('paySlip', function ($query) use ($date) {
                $query->where('month', $date['month'])
                    ->where('years', $date['years']);
            })
            ->withSum(['paySlip' => function ($query) use ($date) {
                $query->where('month', $date['month'])
                    ->where('years', $date['years']);
            }], 'amount');

        if (!empty($this->employeeId)) {
            $dataPayroll = $data->whereIn('users.id', $this->employeeId)->get();
        } else {
            $dataPayroll = $data->get();
        }

        $data = [];

        foreach ($dataPayroll as $payroll) {
            // FOR EMPLOYEE DATA
            $rowData = [];

            // FOR SALARY AMOUNT
            $rowData2 = [];

            // FOR NETT SALARY
            $rowData3 = [];

            for ($i = 0; $i < count($headers); $i++) {
                // $rowData[] = $payroll[$headers[$i]['value']];
                $rowData = [
                    $payroll->id,
                    $payroll->nip,
                    $payroll->firstname . ' ' . $payroll->lastname,
                    ucwords($payroll->job_position),
                    $payroll->division_name,
                    $payroll->job_level,
                    $payroll->job_status_name,
                ];
            }

            for ($j = 0; $j < count($headers2); $j++) {
                $rowData2[] = $this->getSalaryBySalarName($payroll->id, $headers2[$j]);
            }

            $columnHeaders1 = array_merge($rowData, $rowData2);

            $rowData3[] = $this->getNettSalary($payroll->id);

            $data[] = array_merge($columnHeaders1, $rowData3);
        }

        $fileName = 'Payslip-'. getMonthName($this->month) . '-'. $this->year;
        $dir = 'export/payslip/'. $fileName .'.xlsx';
        Excel::store(
            new GenerateExcel('Payslip Report', [array_merge($headers, $headers2, $headers3)], $data, ['header_style' =>
            ['background' => 'F4FF90', 'bold' => true, 'auto_size' => true], 'column_style' =>
            ['A' => ['protection' => false]], 'body_style' => ['protection' => false], 'freeze_pane' => 'B2']),
            $dir,
            'public'
        );
        return URL::to(Storage::url($dir));
    }

}
