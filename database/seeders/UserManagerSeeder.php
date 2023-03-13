<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
use App\Models\Permission;
use App\Models\Role;
use App\Models\UserManager;
use App\Models\UserManagerAssign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


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
            $permissions = Permission::all();
            $branch = CompanyBranch::first();
            // $role = Role::where('branch_id', $branch->id)->where('name', 'hr')->first();
            $role = Role::create([
                'name' => 'Head Branch '.$branch->branch_name,
                'guard_name' => 'sanctum:manager',
                'is_role_manager' => 1,
                'branch_id'       => $branch->id,
                'department_id'  => null,
                'created_at'      => Date::now(),
                'updated_at'      => Date::now(),
            ]);

            foreach ($permissions as $permission) {
                $role->syncPermissions($permission);
            }

            $userManager = UserManager::create([
                'name' => 'HR Branch 1',
                'email' => 'hr@mail.com',
                'nip'   => '5443534565',
                'gender' => MALE_GENDER,
                'phone_number' => '0852684568',
                'password' => Hash::make('123123123'),
                'email_verified_at' => Date::now(),
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
                'invited_status' => 1,
            ]);
            $userManagerAssign = UserManagerAssign::create([
                'id'                => 1,
                'uuid'              => Str::uuid(),
                'user_manager_id'   => $userManager->id,
                'branch_id'         => $branch->id,
                'status'            => 1,
                'created_at'        => Date::now(),
                'updated_at'        => Date::now(),
            ]);
            $userManagerAssign->assignRole($role->id);
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage());
        }
    }
}
