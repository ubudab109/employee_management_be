<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateHolidayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            for ($i = 2023; $i <= 2080; $i++) {
                $data = json_decode(file_get_contents(base_path('resources/calendars/'.$i.'.json')), true);
                $holidays = $data['data']['holiday'];
                $leaves = $data['data']['leave'];
                
                foreach ($holidays as $keyHoliday => $holiday) {
                    DB::table('holidays')->insert([
                        'years'       => $data['data']['year'],
                        'month'       => $keyHoliday,
                        'type'        => 'holiday',
                        'month_name'  => getMonthName($keyHoliday),
                        'data'        => is_null($holiday['data']) ? null : json_encode($holiday['data']),
                    ]);
                }
    
                foreach ($leaves as $keyLeave => $leave) {
                    DB::table('holidays')->insert([
                        'years'       => $data['data']['year'],
                        'month'       => $keyLeave,
                        'type'        => 'leave',
                        'month_name'  => getMonthName($keyLeave),
                        'data'        => is_null($leave['data']) ? null : json_encode($leave['data']),
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            throw new \Exception($err->getMessage());
        }
    }
}
