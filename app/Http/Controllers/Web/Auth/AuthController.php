<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\BaseController;
use App\Models\UserManager;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public $rolePermission;

    public function __construct(RolePermissionManagerInterface $rolePermission)
    {
        $this->rolePermission = $rolePermission;
    }
    
    /**
     * Login Process
     * 
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential'        => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error', $validator->errors());
        }

        $user = UserManager::where('email', $request->credential)->orWhere('phone_number', $request->credential)->first();
        if ($user != null) {
            $userBranch = $user->branch()->with('branch')->first();
            if (Hash::check($request->password, $user->password)) {
                if (!$user->hasVerifiedEmail()) {
                    return $request->wantsJson()
                        ? $this->sendError('Email Not Verified', "Please Verified Your Email")
                        : $this->sendResponse(array("success" => false), 'Email not verified');
                }
                $token = $user->createToken('admin_token')->plainTextToken;
                if (config('app.env') == 'development') {
                    $permission = $this->dummyPermissions();
                } else {
                    $permission = $this->dataPermissions($user, $userBranch);
                }

                if ($userBranch != null) {
                    $rolesName = ucfirst($userBranch->roles()->first()->name);
                    $branch = $userBranch;
                } else {
                    $rolesName = ucfirst($user->roles()->first()->name);
                    $branch = null;
                }

                return $this->sendResponse([
                    'token'         => $token,
                    'user_data'     => $user->makeHidden(['roles']),
                    'branch'        => $branch,
                    'expired_token' => Date::now()->addDay(150),
                    'role'          => $rolesName,
                    'permissions'   => $permission
                ], 'Login Successfully');
            }

            return $this->sendUnauthorized('Credential Not Found', null);
        } else {
            return $this->sendUnauthorized('Email or Phone Number Not Found', null);
        }
    }

    /**
     * Validate Token
     * 
     * @return Illuminate\Http\Response
     */
    public function validateToken()
    {
        try {
            if (Auth::guard('sanctum:manager')->check()) {
                return $this->sendResponse(true, 'Validated');
            } else {
                return $this->sendUnauthorized(false, 'Validated');
            }
        } catch (\Exception $th) {
            return $this->sendError('Not Validated', $th->getMessage());
        }
        // return Auth::guard('api')->check();
    }

    /**
     * Logout Process
     * 
     * @return Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, 'Successfully Logout');
    }

    /**
     * GET DATA PERMISSION
     * @param UserManager $user
     * @param UserManagerAssign $userBranch
     * @return array
     */
    private function dataPermissions($user, $userBranch)
    {
        /* ALL DATA PERMISSIONS WILL ASSIGN HERE */
        $data = array();

        /* GET SCOPE PERMISSION FIRST */
        $scopes = DB::table('permission_scope')->orderBy('order')->get();

        foreach ($scopes as $scope) {
            $dataScope['id'] = $scope->id;
            $dataScope['name'] = $scope->name;
            $dataScope['order'] = $scope->order;

            $dataScope['permissions'] = array();
            $permissions = DB::table('permissions')->where('scope_id', $scope->id)->get();

            // check if current scope have permission, if not, then is_scope_access is false
            if (count($permissions) < 1) {
                $dataScope['is_scope_access'] = false;
            }

            /* GET PERMISSIONS FROM PERMISSION SCOPE */
            foreach ($permissions as $permission) {
                $prm['id']              = $permission->id;
                $prm['name']            = $permission->name;
                $prm['display_name']    = $permission->display_name;
                $prm['guard_name']      = $permission->guard_name;
                $prm['order']           = $permission->order;

                // check if current user doesn't have branch (it means this user is superadmin)
                // the purpose for this checking is for permissions in company branch feature
                // because the only user can access the company branch feature is superadmin or the user that directly assigned
                // to branch prefix permissions. e.g (branch-list, branch-detail, etc) not from role
                if ($userBranch == null) {
                    // check if current permission is have scope id with 7 value (it means this permissions is associated with branch)
                    // the purpose for this checking because the branch permissions is directly assigned to user not from role
                    if ($permission->scope_id == 7) {
                        $prm['is_assigned']     = $user->hasPermissionTo($permission->name) ? true : false;
                        array_push($dataScope['permissions'], $prm);
                    } else {
                        $prm['is_assigned']     = $user->roles()->first()->hasPermissionTo($permission->name) ? true : false;
                        array_push($dataScope['permissions'], $prm);
                    }
                } else {
                    $prm['is_assigned']     = $userBranch->roles()->first()->hasPermissionTo($permission->name) ? true : false;
                    $dataScope['permissions'][] = $prm;
                }
                // Count how many permission in current scope was assigned. If at least have one, then key 'is_scope_access' was true and if not then false
                $dataScope['is_scope_access'] = arrFilterCount($dataScope['permissions'], 'is_assigned', true) > 0 ? true : false;
            }
            $data[] = $dataScope;
        }

        return $data;
    }

    /**
     * DUMMY PERMISSIONS
     * @return array
     */
    private function dummyPermissions()
    {
        return [
            [
                "id" => 1,
                "name" => "Dashboard",
                "order" => 1,
                "permissions" => [
                    [
                        "id" => 121,
                        "name" => "dashboard-page",
                        "display_name" => "Dashboard",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 2,
                "name" => "Attendance",
                "order" => 2,
                "permissions" => [
                    [
                        "id" => 142,
                        "name" => "attendance-management-list",
                        "display_name" => "List Attendance",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 143,
                        "name" => "attendance-management-detail",
                        "display_name" => "Detail Attendance",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 162,
                        "name" => "attendance-management-edit",
                        "display_name" => "Edit Attendance",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 3,
                "name" => "Payroll",
                "order" => 3,
                "permissions" => [
                    [
                        "id" => 144,
                        "name" => "payroll-management-list",
                        "display_name" => "Payroll List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 4,
                "name" => "Schedule",
                "order" => 4,
                "permissions" => [
                    [
                        "id" => 145,
                        "name" => "schedule-request-list",
                        "display_name" => "Schedule Request List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 5,
                "name" => "Employee",
                "order" => 5,
                "permissions" => [
                    [
                        "id" => 122,
                        "name" => "division-management-list",
                        "display_name" => "List Division",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 123,
                        "name" => "division-management-detail",
                        "display_name" => "Detail Division",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 124,
                        "name" => "division-management-create",
                        "display_name" => "Create Division",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 125,
                        "name" => "division-management-update",
                        "display_name" => "Update Division",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 126,
                        "name" => "division-management-delete",
                        "display_name" => "Delete Division",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 127,
                        "name" => "employee-management-list",
                        "display_name" => "List Employee",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 128,
                        "name" => "employee-management-detail",
                        "display_name" => "Detail Employee",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 129,
                        "name" => "employee-management-update",
                        "display_name" => "Update Employee",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 130,
                        "name" => "employee-management-delete",
                        "display_name" => "Delete Employee",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 146,
                        "name" => "department-list",
                        "display_name" => "Department List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 147,
                        "name" => "department-detail",
                        "display_name" => "Department Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 148,
                        "name" => "department-create",
                        "display_name" => "Create Department",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 149,
                        "name" => "department-update",
                        "display_name" => "Edit Department",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 150,
                        "name" => "department-delete",
                        "display_name" => "Delete Department",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 151,
                        "name" => "job-status-list",
                        "display_name" => "Job Status List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 152,
                        "name" => "job-status-detail",
                        "display_name" => "Job Status Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 153,
                        "name" => "job-status-create",
                        "display_name" => "Create Job Status",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 154,
                        "name" => "job-status-update",
                        "display_name" => "Update Job Status",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 155,
                        "name" => "job-status-delete",
                        "display_name" => "Delete Job Status",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 161,
                        "name" => "employee-management-create",
                        "display_name" => "Create Employee",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 6,
                "name" => "Management",
                "order" => 6,
                "permissions" => [
                    [
                        "id" => 131,
                        "name" => "role-permission-list",
                        "display_name" => "List Role Permissions",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 132,
                        "name" => "role-permission-detail",
                        "display_name" => "Detail Role Permissions",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 133,
                        "name" => "role-permission-create",
                        "display_name" => "Create Role Permissions",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 134,
                        "name" => "role-permission-update",
                        "display_name" => "Update Role Permissions",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 135,
                        "name" => "role-permission-delete",
                        "display_name" => "Update Role Permissions",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 136,
                        "name" => "user-management-permission-list",
                        "display_name" => "List User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 137,
                        "name" => "user-management-permission-detail",
                        "display_name" => "Detail User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 138,
                        "name" => "user-management-permission-create",
                        "display_name" => "Invite User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 139,
                        "name" => "user-management-permission-update",
                        "display_name" => "Update User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 140,
                        "name" => "user-management-permission-delete",
                        "display_name" => "Remove or Cancel User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 141,
                        "name" => "user-management-permission-resend",
                        "display_name" => "Resend Invite User Management",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 7,
                "name" => "Branch",
                "order" => 7,
                "permissions" => [
                    [
                        "id" => 156,
                        "name" => "branch-list",
                        "display_name" => "List Company Branch",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 157,
                        "name" => "branch-detail",
                        "display_name" => "Detail Company Branch",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 158,
                        "name" => "branch-create",
                        "display_name" => "Create Company Branch",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 159,
                        "name" => "branch-update",
                        "display_name" => "Update Company Branch",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 160,
                        "name" => "branch-delete",
                        "display_name" => "Delete Company Branch",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 8,
                "name" => "Employee Overtime",
                "order" => 8,
                "permissions" => [
                    [
                        "id" => 163,
                        "name" => "employee-overtime-list",
                        "display_name" => "Employee Overtime List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 164,
                        "name" => "employee-overtime-detail",
                        "display_name" => "Employee Overtime Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 166,
                        "name" => "employee-overtime-update",
                        "display_name" => "Employee Overtime Update",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 167,
                        "name" => "employee-overtime-delete",
                        "display_name" => "Employee Overtime Delete",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 168,
                        "name" => "employee-overtime-assign",
                        "display_name" => "Employee Overtime Assign Payroll",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 9,
                "name" => "Employee Paid Leave",
                "order" => 9,
                "permissions" => [
                    [
                        "id" => 169,
                        "name" => "employee-leave-list",
                        "display_name" => "Employee Leave List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 170,
                        "name" => "employee-leave-detail",
                        "display_name" => "Employee Leave Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 171,
                        "name" => "employee-leave-update",
                        "display_name" => "Employee Leave Update",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 172,
                        "name" => "employee-leave-delete",
                        "display_name" => "Employee Leave Delete",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 10,
                "name" => "Employee Reimbursement",
                "order" => 10,
                "permissions" => [
                    [
                        "id" => 173,
                        "name" => "employee-reimbursement-list",
                        "display_name" => "Employee Reimbersement List",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 174,
                        "name" => "employee-reimbursement-detail",
                        "display_name" => "Employee Reimbersement Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 175,
                        "name" => "employee-reimbursement-update",
                        "display_name" => "Employee Reimbersement Update",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ],
            [
                "id" => 11,
                "name" => "Finance",
                "order" => 11,
                "permissions" => [
                    [
                        "id" => 178,
                        "name" => "payslip-detail",
                        "display_name" => "Payslip Detail",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 179,
                        "name" => "payslip-edit",
                        "display_name" => "Payslip Edit",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 180,
                        "name" => "payslip-generate",
                        "display_name" => "Payslip Generate",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ],
                    [
                        "id" => 181,
                        "name" => "payslip-send",
                        "display_name" => "Payslip Send",
                        "guard_name" => "sanctum:manager",
                        "order" => null,
                        "is_assigned" => true
                    ]
                ],
                "is_scope_access" => true
            ]
        ];
    }
}
