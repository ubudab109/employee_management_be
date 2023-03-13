<?php

namespace App\Jobs;

use App\Models\CompanyBranch;
use App\Models\PayrollGenerateProcess;
use App\Models\User;
use App\Services\PayrollServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class GeneratePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payrollServices, $process, $branchSelected, $requestData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PayrollServices $payrollServices, PayrollGenerateProcess $process, $requestData)
    {
        $this->payrollServices = $payrollServices;
        $this->process = $process;
        $this->branchSelected = CompanyBranch::find(branchSelected('sanctum:manager')->id);
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->process->status = GENERATING;
        $this->process->message = null;
        $this->process->save();
        try {
            $this->generatingPayroll();
            $this->process->status = GENERATED;
            $this->process->message = "Payslip Generated Successfully";
            $this->process->save();
        } catch (\Exception $err) {
            $this->process->status = FAILED;
            $this->process->message = $err->getMessage();
            $this->process->save();
            Log::error($err->getMessage());
            throw $err;
        }
    }

    /**
     * GENERATING PAYSLIP FROM PAYROLL
     * @return boolean
     * @throws \Exception
     */
    public function generatingPayroll()
    {
        if ($this->requestData['type'] == 'all') {
            $employeeSalaryData = $this->branchSelected->employeeSalary()->get();
        } else if ($this->requestData['type'] == 'selected') {
            $employeeSalaryData = $this->branchSelected->employeeSalary()->whereIn('employee_id', $this->requestData['employee_id'])->get();
        } else {
            $employeeSalaryData = $this->branchSelected->employeeSalary()->get();
        }
        foreach ($employeeSalaryData as $employeeSalary) {
            $employee = User::find($employeeSalary->employee_id);
            $currentDate = date("d");
            if ($payrollCodeSetting = settings('payroll_code') != null) {
                $payrollCode = $payrollCodeSetting.'-'.$employee->nip.'-'.$currentDate.'/'.$this->requestData['month'].'/'.$this->requestData['years'];
            } else {
                $payrollCode = 'Payslip'.'-'.$employee->nip.'-'.$currentDate.'/'.$this->requestData['month'].'/'.$this->requestData['years'];
            }
            $param = [
                'branch_id'     => $this->branchSelected->id,
                'employee_id'   => $employeeSalary->employee_id,
                'department_id' => $employee->branch->division_id,
                'salary_name'   => $employeeSalary->salaryComponent->name,
                'month'         => $this->requestData['month'],
                'years'         => $this->requestData['years'],
            ];
            $data = [
                'payroll_code'  => $payrollCode,
                'amount'        => $employeeSalary->amount,
                'type'          => $employeeSalary->type,
                'generate_date' => Date::now(),
                'status'        => GENERATED,
            ];
            $dataNew = array_merge($param, $data);
            $isGenerated = $this->payrollServices->storeOrUpdate($param, $dataNew);
            if (!$isGenerated['status']) {
                throw new \Exception('Unable to generate the payslip. Please re-generate this payslip');
            }
        }

        return true;
    }
}
