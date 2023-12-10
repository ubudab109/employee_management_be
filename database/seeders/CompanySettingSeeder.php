<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataCompany = [
            [
                'setting_key'   => 'company_name',
                'setting_name'  => 'Company Name',
                'value'         => 'My Company',
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ],
            [
                'setting_key'   => 'company_logo',
                'setting_name'  => 'Company Logo',
                'value'         => null,
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ],
            [
                'setting_key'   => 'company_entry_hours',
                'setting_name'  => 'Clock In',
                'value'         => '09:00',
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ],
            [
                'setting_key'   => 'company_out_hours',
                'setting_name'  => 'Clock Out',
                'value'         => '18:00',
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ],
        ];
        CompanySetting::insert($dataCompany);
    }
}
