<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UserManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $branch = CompanyBranch::first();
            $role = Role::where('branch_id', $branch->id)->where('name', 'hr')->first();
            $userManager = UserManager::create([
                'name' => 'HR Branch 1',
                'email' => 'hr@mail.com',
                'nip'   => '5443534565',
                'gender' => MALE_GENDER,
                'phone_number' => '',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
                'invited_status' => 1,
            ]);
            $userManager->assignRole($role->id);
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage());
        }
    }
}
