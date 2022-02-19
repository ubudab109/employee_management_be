<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionScope;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
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
            $role = Role::where('name', 'superadmin')->first();

            $dataPermissionScopeManager = [
                [
                    'id' => 1,
                    'name' => 'Dashboard',
                    'order' => 1,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 2,
                    'name' => 'Attendance',
                    'order' => 2,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 3,
                    'name' => 'Payroll',
                    'order' => 3,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 4,
                    'name' => 'Schedule',
                    'order' => 4,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 5,
                    'name' => 'Employee',
                    'order' => 5,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 6,
                    'name' => 'Management',
                    'order' => 6,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
            ];
    
            PermissionScope::insert($dataPermissionScopeManager);
    
            $dataPermissionsManager = [
                [
                    'scope_id'          => 1,
                    'name'              => 'dashboard-page',
                    'display_name'      => 'Dashboard',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-list',
                    'display_name'      => 'List Division',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-detail',
                    'display_name'      => 'Detail Division',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-create',
                    'display_name'      => 'Create Division',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-update',
                    'display_name'      => 'Update Division',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-delete',
                    'display_name'      => 'Delete Division',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'job-status-management-list',
                    'display_name'      => 'List Job Status',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'job-status-management-detail',
                    'display_name'      => 'Detail Job Status',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'job-status-management-create',
                    'display_name'      => 'Create Job Status',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'job-status-management-update',
                    'display_name'      => 'Update Job Status',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'job-status-management-delete',
                    'display_name'      => 'Delete Job Status',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-list',
                    'display_name'      => 'List Employee',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-detail',
                    'display_name'      => 'Detail Employee',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-update',
                    'display_name'      => 'Update Employee',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-delete',
                    'display_name'      => 'Delete Employee',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-list',
                    'display_name'      => 'List Role Permissions',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-detail',
                    'display_name'      => 'Detail Role Permissions',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-create',
                    'display_name'      => 'Create Role Permissions',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-update',
                    'display_name'      => 'Update Role Permissions',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-delete',
                    'display_name'      => 'Update Role Permissions',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-list',
                    'display_name'      => 'List User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-detail',
                    'display_name'      => 'Detail User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-create',
                    'display_name'      => 'Invite User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-update',
                    'display_name'      => 'Update User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-delete',
                    'display_name'      => 'Remove or Cancel User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-resend',
                    'display_name'      => 'Resend Invite User Management',
                    'guard_name'        => 'auth:sanctum',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
            ];
    
            Permission::insert($dataPermissionsManager);
    
            foreach(Permission::all() as $permissions) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissions->id,
                    'role_id' => $role->id
                ]);
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage().''. $err->getLine());
        } 
    }
}
