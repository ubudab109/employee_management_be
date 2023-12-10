<?php

namespace Database\Seeders;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $employee = User::first();
        try {
            for ($i = 2; $i < 600; $i++) {
                $attendance = EmployeeAttendance::create([
                    'id'            => $i,
                    'employee_id'   => $employee->id,
                    'work_places'   => $i % 2 === 0 ? '0' : '1',
                    'branch_id'     => 7,
                    'status_clock'  => $i % 2 === 0 ? '0' : ($i % 3 === 0 ? '1' : '2'),
                    'clock_in'      => $i % 2 === 0 ? '09:00:00' : ($i % 3 === 0 ? '09:45:00' : null),
                    'clock_out'     => $i % 2 === 0 ? '17:00:00' : ($i % 3 === 0 ? '17:45:00' : null),
                ]);

            }

            for ($i = 2; $i < 600; $i++) {
                if ($i % 2 === 0 || $i % 3 === 0) {
                    $clokcIn = EmployeeAttendanceLocation::create([
                        'employee_attendance_id'    => $i,
                        'latitude'      => '-6.287104587796341',
                        'longitude'     => '106.82131682466287',
                        'clock_type'    =>  '0',
                        'location'      => 'Rumah Sakit Kemang, Jl. Ampera Raya'
                    ]);
                    if ($clokcIn->employeeAttendance->work_places == '1') {
                        $clokcIn->files()->create([
                            'files' => 'https://farm4.staticflickr.com/3511/3244469731_3cdbb1192d.jpg',
                            'type'  => 'images',
                        ]);
                    }
                }
            }

            for ($i = 2; $i < 600; $i++) {
                if ($i % 2 === 0 || $i % 3 === 0) {
                    $clockOut = EmployeeAttendanceLocation::create([
                        'employee_attendance_id'    => $i,
                        'latitude'      => '-6.287104587796341',
                        'longitude'     => '106.82131682466287',
                        'clock_type'    =>  '1',
                        'location'      => 'Rumah Sakit Kemang, Jl. Ampera Raya'
                    ]);

                    if ($clockOut->employeeAttendance->work_places == '1') {
                        $clockOut->files()->create([
                            'files' => 'https://farm4.staticflickr.com/3511/3244469731_3cdbb1192d.jpg',
                            'type'  => 'images',
                        ]);
                    }
                }
            }


            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            print_r($err->getMessage());
        }
    }
}
