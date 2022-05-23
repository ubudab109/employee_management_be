<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
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

            /** BRANCH */
            $branch = CompanyBranch::first();

            /* DEPARTMENT */
            $department = CompanyDivision::create([
                'division_code' => 24971,
                'branch_id'     => $branch->id,
                'division_name' => 'Human Resource',
            ]);

            /** USER MANAGER */
            $superadmin = UserManager::create([
                'name' => 'superadmin',
                'email' => 'superadmin@admin.com',
                'nip'   => '',
                'gender' => MALE_GENDER,
                'phone_number' => '',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
                'invited_status' => 1,
            ]);

            $superadmin->assignRole('superadmin');
            $superadmin->givePermissionTo([
                'branch-list',
                'branch-detail',
                'branch-create',
                'branch-update',
                'branch-delete',
            ]);
            /** END USER MANAGER */

            /** EMPLOYEE */
            $hrdRole = Role::create([
                'name'              => 'hr',
                'is_role_manager'   => false,
                'branch_id'         => $branch->id,
                'department_id'     => $department->id, 
                'guard_name'        => 'auth:sanctum',
                'created_at'        => Date::now(),
                'updated_at'        => Date::now(),
            ]);

            $hr = User::create([
                'name' => 'HR',
                'email' => 'hr@tdi.com',
                'nip'   => '23432432',
                'gender' => MALE_GENDER,
                'phone_number' => '08596324321',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);

            $assignHr = UserDivisionAssign::create([
                'id'            => 1,
                'uuid'          => (string)Str::uuid(),
                'user_id'       => $hr->id,
                'division_id'   => $department->id,
                'branch_id'     => $branch->id,
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
