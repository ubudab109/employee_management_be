<?php

namespace Database\Seeders;

use App\Models\CompanyDivision;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDivisionAssign;
use App\Models\UserManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserWithDivisionSeeder extends Seeder
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

            /** USER MANAGER */
            $superAdminRole = Role::create([
                'name' => 'superadmin',
                'guard_name' => 'auth:sanctum',
                'is_role_manager' => true,
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            

            $superadmin = UserManager::create([
                'name' => 'owner',
                'email' => 'owner@tdi.com',
                'nip'   => '234534534',
                'phone_number' => '0859685489',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
                'invited_status' => 1,
            ]);
            $superadmin->assignRole($superAdminRole);
            /** END USER MANAGER */

            /** EMPLOYEE */
            $hrdRole = Role::create([
                'name' => 'hr',
                'is_role_manager' => false,
                'guard_name' => 'auth:sanctum',
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            $hr = User::create([
                'name' => 'HR',
                'email' => 'hr@tdi.com',
                'nip'   => '23432432',
                'phone_number' => '08596324321',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            $division = CompanyDivision::create([
                'division_code' => 'H23123',
                'division_name' => 'Personalia',
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            $assignHr = UserDivisionAssign::create([
                'id'            => 1,
                'uuid'          => (string)Str::uuid(),
                'user_id'       => $hr->id,
                'division_id'   => $division->id,
                'status'        => true,
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            $assignHr->assignRole($hrdRole);
            /** END EMPLOYEE */

            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage() . ' ' . $err->getLine());
        }
    }
}
