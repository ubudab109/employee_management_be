<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsertOvertimeToPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            Log::info($this->data);
            Log::info(getTotalAmountOvertime($this->data['employee']->gros_salary, $this->data['taken_hour']));
            DB::table('payroll')
                ->insert([
                    'branch_id'     => $this->data['branch_id'],
                    'employee_id'   => $this->data['employee_id'],
                    'department_id' => $this->data['department_id'],
                    'payroll_code'  => $this->data['payroll_code'],
                    'salary_name'   => 'Overtime',
                    'amount'        => getTotalAmountOvertime($this->data['gross_salary'], $this->data['taken_hour']),
                    'type'          => SALARY_INCOME,
                    'month'         => $this->data['month'],
                    'years'         => $this->data['years'],
                    'generate_date' => $this->data['generate_date'],
                    'status'        => $this->data['status'],
                    'created_at'    => Date::now(),
                    'updated_at'    => Date::now(),
                ]);
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            throw new \Exception($err->getMessage());
        }
    }
}
