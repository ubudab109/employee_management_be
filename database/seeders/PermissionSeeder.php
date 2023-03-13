<?php

namespace Database\Seeders;

use App\Models\CompanyBranch;
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
            $branch = CompanyBranch::create([
                'branch_name'   => 'Branch 1',
                'branch_code'   => '',
                'branch_order'  => 1,
                'is_centered'   => true,
                'province_id'   => 31,
                'regency_id'    => 3171,
                'district_id'   => 3171020,
                'villages_id'   => 3171020002,
                'address'       => 'Jalan Ampera Raya, GG Kancil RT 003/09 ',
                'status'        => '1',
                'latitude'      => '-6.28818289305121',
                'langitude'     => '106.82770635966614',
                'attendance_radius' => 500,
            ]);
            $role = Role::create([
                'name' => 'superadmin',
                'guard_name' => 'sanctum:manager',
                'is_role_manager' => true,
                'branch_id'       => $branch->id,
                'created_at'    => Date::now(),
                'updated_at'    => Date::now(),
            ]);
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
                [
                    'id' => 7,
                    'name' => 'Branch',
                    'order' => 7,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 8,
                    'name' => 'Employee Overtime',
                    'order' => 8,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 9,
                    'name' => 'Employee Paid Leave',
                    'order' => 9,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 10,
                    'name' => 'Employee Reimbersement',
                    'order' => 10,
                    'created_at' => Date::now(),
                    'updated_at' => Date::now(),
                ],
                [
                    'id' => 11,
                    'name' => 'Finance',
                    'order' => 11,
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
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-list',
                    'display_name'      => 'List Division',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-detail',
                    'display_name'      => 'Detail Division',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-create',
                    'display_name'      => 'Create Division',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-update',
                    'display_name'      => 'Update Division',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'division-management-delete',
                    'display_name'      => 'Delete Division',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-list',
                    'display_name'      => 'List Employee',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-create',
                    'display_name'      => 'Create Employee',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-detail',
                    'display_name'      => 'Detail Employee',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-update',
                    'display_name'      => 'Update Employee',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'employee-management-delete',
                    'display_name'      => 'Delete Employee',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-list',
                    'display_name'      => 'List Role Permissions',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-detail',
                    'display_name'      => 'Detail Role Permissions',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-create',
                    'display_name'      => 'Create Role Permissions',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-update',
                    'display_name'      => 'Update Role Permissions',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'role-permission-delete',
                    'display_name'      => 'Update Role Permissions',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-list',
                    'display_name'      => 'List User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-detail',
                    'display_name'      => 'Detail User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-create',
                    'display_name'      => 'Invite User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-update',
                    'display_name'      => 'Update User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-delete',
                    'display_name'      => 'Remove or Cancel User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 6,
                    'name'              => 'user-management-permission-resend',
                    'display_name'      => 'Resend Invite User Management',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 2,
                    'name'              => 'attendance-management-list',
                    'display_name'      => 'List Attendance',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 2,
                    'name'              => 'attendance-management-detail',
                    'display_name'      => 'Detail Attendance',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 2,
                    'name'              => 'attendance-management-edit',
                    'display_name'      => 'Edit Attendance',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 3,
                    'name'              => 'payroll-management-list',
                    'display_name'      => 'Payroll List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 4,
                    'name'              => 'schedule-request-list',
                    'display_name'      => 'Schedule Request List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'department-list',
                    'display_name'      => 'Department List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'department-detail',
                    'display_name'      => 'Department Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'department-create',
                    'display_name'      => 'Create Department',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'department-update',
                    'display_name'      => 'Edit Department',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 5,
                    'name'              => 'department-delete',
                    'display_name'      => 'Delete Department',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 7,
                    'name'              => 'branch-list',
                    'display_name'      => 'List Company Branch',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 7,
                    'name'              => 'branch-detail',
                    'display_name'      => 'Detail Company Branch',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 7,
                    'name'              => 'branch-create',
                    'display_name'      => 'Create Company Branch',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 7,
                    'name'              => 'branch-update',
                    'display_name'      => 'Update Company Branch',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 7,
                    'name'              => 'branch-delete',
                    'display_name'      => 'Delete Company Branch',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 8,
                    'name'              => 'employee-overtime-list',
                    'display_name'      => 'Employee Overtime List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 8,
                    'name'              => 'employee-overtime-detail',
                    'display_name'      => 'Employee Overtime Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 8,
                    'name'              => 'employee-overtime-update',
                    'display_name'      => 'Employee Overtime Update',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 8,
                    'name'              => 'employee-overtime-delete',
                    'display_name'      => 'Employee Overtime Delete',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 8,
                    'name'              => 'employee-overtime-assign',
                    'display_name'      => 'Employee Overtime Assign Payroll',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 9,
                    'name'              => 'employee-leave-list',
                    'display_name'      => 'Employee Leave List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 9,
                    'name'              => 'employee-leave-detail',
                    'display_name'      => 'Employee Leave Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 9,
                    'name'              => 'employee-leave-update',
                    'display_name'      => 'Employee Leave Update',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 9,
                    'name'              => 'employee-leave-delete',
                    'display_name'      => 'Employee Leave Delete',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                // REIMBERSEMENT
                [
                    'scope_id'          => 10,
                    'name'              => 'employee-reimbursement-list',
                    'display_name'      => 'Employee Reimbersement List',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 10,
                    'name'              => 'employee-reimbursement-detail',
                    'display_name'      => 'Employee Reimbersement Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 10,
                    'name'              => 'employee-reimbursement-update',
                    'display_name'      => 'Employee Reimbersement Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                // PAYROLL
                [
                    'scope_id'          => 11,
                    'name'              => 'payslip-detail',
                    'display_name'      => 'Payslip Detail',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 11,
                    'name'              => 'payslip-edit',
                    'display_name'      => 'Payslip Edit',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 11,
                    'name'              => 'payslip-generate',
                    'display_name'      => 'Payslip Generate',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                [
                    'scope_id'          => 11,
                    'name'              => 'payslip-send',
                    'display_name'      => 'Payslip Send',
                    'guard_name'        => 'sanctum:manager',
                    'created_at'        => Date::now(),
                    'updated_at'        => Date::now(),
                ],
                


            ];
    
            Permission::insert($dataPermissionsManager);
    
            foreach(Permission::all() as $permissions) {
                if ($permissions->scope_id != 7) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $permissions->id,
                        'role_id' => $role->id
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            dd($err->getMessage().''. $err->getLine());
        } 
    }
}
