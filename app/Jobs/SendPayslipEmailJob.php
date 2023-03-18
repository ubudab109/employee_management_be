<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PayrollStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PDF;

class SendPayslipEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month, $year, $branchId, $type;
    protected $employeeId = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($month, $year, $branchId, $type, $employeeId = [])
    {
        $this->month = $month;
        $this->year = $year;
        $this->branchId = $branchId;
        $this->type = $type;
        $this->employeeId = $employeeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->type == 'selected') {
            $employees = User::whereIn('id', $this->employeeId)->get();
        } else if ($this->type == 'all') {
            $employees = User::whereHas('branch', function ($query) {
                $query->where('branch_id', $this->branchId);
            })->whereHas('paySlip', function ($query) {
                $query->where('month', $this->month)
                    ->where('years', $this->year);
            })->get();
        } else {
            throw new \Exception("Send typ is invalid");
        }
        foreach ($employees as $employee) {
            $checkStatusPayslip = $employee->payslipStatus()->where('month', $this->month)->where('years', $this->year)->first();
            if ($checkStatusPayslip->status !== SENDED) {
                $generatePdf = $this->generatePdf($employee);
                $user = [
                    'user' => [
                        'name'  => $employee->firstname . ' ' . $employee->lastname,
                        'url'   => $generatePdf,
                    ]
                ];
                Mail::send('email.payslipEmail', $user, function ($message) use ($generatePdf, $employee) {
                    $message->to($employee->email, $employee->email)
                        ->subject('Payslip ' . $this->month . '/' . $this->year);
    
                    $message->attach($generatePdf);
                });
            }
        }
    }

    /**
     * The above function is used to generate a PDF file.
     * 
     * @return string return value is the URL of the PDF file.
     * @throws \Exception
     */
    private function generatePdf($employee)
    {
        DB::beginTransaction();
        try {
            $grossAmount = $employee->paySlip()->where('month', $this->month)->where('years', $this->year)->where('type', SALARY_INCOME)->sum('amount');
            $deductionAmount = $employee->paySlip()->where('month', $this->month)->where('years', $this->year)->where('type', SALARY_CUTS)->sum('amount');
            $nettAmount = $grossAmount - (-$deductionAmount);
            $bank = $employee->bank()->first();
            $payrollCode =  $employee->paySlip()->where('month', $this->month)->where('years', $this->year)->first()->payroll_code;
            $item = [
                'nip'              => $employee->nip,
                'division_name'    => $employee->getDivisionNameAttribute(),
                'firstname'        => $employee->firstname,
                'lastname'         => $employee->lastname,
                'gender'           => ucwords($employee->gender),
                'month'            => getMonthName($this->month),
                'years'            => $this->year,
                'payslip_code'     => $payrollCode,
                'bank_name'        => $bank->bank_name,
                'account_number'   => $bank->account_number,
                'job_position'     => ucwords($employee->job_position),
                'salary_income'    => $employee->paySlip()->where('month', $this->month)->where('years', $this->year)->where('type', SALARY_INCOME)->get(),
                'salary_cuts'      => $employee->paySlip()->where('month', $this->month)->where('years', $this->year)->where('type', SALARY_CUTS)->get(),
                'gross_amount'     => $grossAmount,
                'deduction_amount' => -$deductionAmount,
                'nett_amount'      => $nettAmount,
            ];
            $pdf = PDF::loadView('payslip', ['item' => $item]);
            $content = $pdf->download()->getOriginalContent();
            $getDisk = config('filesystems.default');
            Storage::disk($getDisk)->put('public/payslip/' . $employee->nip . '/' . $employee->nip . '-' . $this->month . '-' . $this->year . '.pdf', $content);
            if ($getDisk != 'local') {
                $url = Storage::disk($getDisk)->url('payslip/' . $employee->nip . '/' . $employee->nip . '-' . $this->month . '-' . $this->year . '.pdf');
            } else {
                $url = URL::to(Storage::disk($getDisk)->url('payslip/' . $employee->nip . '/' . $employee->nip . '-' . $this->month . '-' . $this->year . '.pdf'));
            }
            
            /** INSERT PAYSLIP PDF TO EMPLOYEE */
            DB::table('payslip_pdf')->insert([
                'employee_id'   => $employee->id,
                'years'         => $this->year,
                'month'         => $this->month,
                'generate_date' => Date::now(),
                'file'          => $url,
            ]);

            /** UPDATE EMPLOYEE PAYSLIP STATUS TO SENDED */
            $employee->payslipStatus()->where('month', $this->month)->where('years', $this->year)->first()->update([
                'status'    => SENDED,
            ]);
            $employee->paySlip()->where('month', $this->month)->where('years', $this->year)
            ->update([
                'status' => SENDED
            ]);
            
            $dataPayroll = DB::table('payroll')
            ->where('month', $this->month)
            ->where('years', $this->year)
            ->where('branch_id', $this->branchId)
            ->get()->groupBy('employee_id');
            $totalEmployeeInPayroll = count($dataPayroll->toArray());
            $totalEmployeePayslipSend = DB::table('employee_payslip_status')
            ->where('branch_id', $this->branchId)
            ->where('month', $this->month)
            ->where('years', $this->year)
            ->where('status', SENDED)
            ->count();

            /* 
                IF TOTAL PAYSLIP SEND IS MORE THAN 0 BUT NOT EQUAL TO EMPLOYEE PAYROLL THEN UPDATE TO PARTIALLY SENDED

                IF TOTAL EMPLOYEE IN CURRENT PERIOD PAYSLIP IS EQUAL TO TOTAL EMPLOYEE PAYSLIP THAT HAD BEEN SENT IN CURRENT PERIOD
                THEN UPDATE PAYROLL STATUS IN CURRENT PERIOD TO ALL SENDED

                ELSE, THEN UPDATE TO GENERATED
            */
            if ($totalEmployeePayslipSend > 0 && $totalEmployeeInPayroll != $totalEmployeePayslipSend) {
                PayrollStatusService::updateOrStore($this->branchId, $this->month, $this->year, PARTIALLY_SENDED);
            } else if ($totalEmployeeInPayroll == $totalEmployeePayslipSend) {
                PayrollStatusService::updateOrStore($this->branchId, $this->month, $this->year, ALL_SENDED);
            } else {
                PayrollStatusService::updateOrStore($this->branchId, $this->month, $this->year, GENERATED);
            }
            DB::commit();
            return $url;
        } catch (\Exception $err) {
            DB::rollBack();
            throw new \Exception($err->getMessage());
        }
    }
}
