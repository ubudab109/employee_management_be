<?php

namespace App\Http\Controllers\Web\CompanyBranch;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Repositories\CompanyBranch\CompanyBranchInterface;
use App\Repositories\RolePermissionManager\RolePermissionManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyBranchController extends BaseController
{

    public $companyBranch, $role;

    public function __construct(CompanyBranchInterface $companyBranch, RolePermissionManagerInterface $role)
    {
        $this->companyBranch = $companyBranch;
        $this->role = $role;
    }

    /**
     * Display a listing of the company branch.
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->show != null && $request->show != 'all') {
            $data = $this->companyBranch->getPaginateBranch(
                $request->keyword,
                $request->province,
                $request->regency,
                $request->district,
                $request->villages,
                $request->status,
                $request->show
            );
            $res = new PaginationResource($data);
        } else {
            $res = $this->companyBranch->getAllBranch(
                $request->keyword,
                $request->province,
                $request->regency,
                $request->district,
                $request->villages,
                $request->status,
            );
        }

        return $this->sendResponse($res, 'Data Fetched Successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'branch_name'           => 'required',
            'branch_code'           => '',
            'province_id'           => 'required',
            'regency_id'            => 'required',
            'district_id'           => 'required',
            'villages_id'           => 'required',
            'is_radius_active'      => 'required',
            'address'               => 'required',
            'latitude'              => 'required',
            'longitude'             => 'required',
            'attendance_radius'     => 'required_if:is_radius_active,1',
            'work_type'             => 'required',
        ]);

        if ($validate->fails()) {
            return $this->sendBadRequest('Validator Error', $validate->errors());
        }
        $permissions = $this->role->listAllPermission([
            ['name' ,'not like', '%branch%']
        ]);

        DB::beginTransaction();
        try {
            $input = $request->all();
            $getLastOrder = $this->companyBranch->getLastBranch();
            if ($request->is_centered == 1) {
                $branch = $this->companyBranch->getFirstBranchByCondition([
                    'is_centered'   => 1,
                ]);
                $this->companyBranch->updateBranch($branch->id, [
                    'is_centered'   => 0,
                ]);

                $input['is_centered'] = 1;
            } else {
                $input['is_centered'] = 0;
            }
            $input['branch_order'] = $getLastOrder + 1;
            $branch = $this->companyBranch->createBranch($input);
            $dataRole = [
                'name'              => 'Head Branch - '.$branch->branch_name,
                'guard_name'        => 'sanctum:manager',
                'branch_id'         => $branch->id,
                'is_role_manager'   => true,
                'is_headbranch'     => true,
            ];
            $dataPermission = [];
            foreach ($permissions as $permission) {
                array_push($dataPermission, $permission->id);
            }
            $this->role->createRolePermission($dataRole, $dataPermission);
            DB::commit();
            return $this->sendResponse(array('success' => true), 'Company Branch Created Successfully');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => false, 'message' => $err->getMessage()), defaultResponseError($err->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->sendResponse($this->companyBranch->detailBranch($id), 'Company Branch Fetched Successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'branch_name'           => 'required',
            'branch_code'           => '',
            'province_id'           => 'required',
            'regency_id'            => 'required',
            'district_id'           => 'required',
            'villages_id'           => 'required',
            'is_radius_active'      => 'required',
            'address'               => '',
            'latitude'              => 'required',
            'longitude'             => 'required',
            'attendance_radius'     => 'required_if:is_radius_active,1',
            'work_type'             => 'required',
        ]);

        if ($validate->fails()) {
            return $this->sendBadRequest('Validator Error', $validate->errors());
        }

        DB::beginTransaction();
        try {
            $input = $request->all();
            if ($request->is_centered == 1) {
                $branch = $this->companyBranch->getFirstBranchByCondition([
                    'is_centered'   => 1,
                ]);
                $this->companyBranch->updateBranch($branch->id, [
                    'is_centered'   => 0,
                ]);

                $input['is_centered'] = 1;
            }
            $this->companyBranch->updateBranch($id, $input);
            DB::commit();
            return $this->sendResponse(array('success' => true), 'Company Branch Updated Successfully');
        } catch (\Exception $err) {
            Log::info($err->getMessage());
            DB::rollBack();
            return $this->sendError(array('success' => false), 'Internal Server Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->companyBranch->deleteBranch($id);
        return $this->sendResponse(array('success' => true), 'Branch Deleted Successfully');
    }

    /**
     * Validating branch code.
     *
     * @param  string $branch_code - String of code branch
     * @return \Illuminate\Http\Response
     */
    public function validateBranchCode($branch_code)
    {
        return $this->sendResponse($this->companyBranch->validateBranchCode($branch_code), 'Checked Successfully');
    }

    /**
     * Change or Assign head branch in current branch
     * @param  \Illuminate\Http\Request  $request
     * @param  int $branchId
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function assignOrChangeHeadBranch(Request $request, $branchId)
    {
        $validator = Validator::make($request->all(), [
            'type'            => 'required|in:assign,change',
            'current_manager' => 'required_if:type,==,change',
            'next_manager'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator errors', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $input = $request->only('type', 'current_manager', 'next_manager');
            $this->companyBranch->assignOrChangeHeadBranch($input['type'], $branchId, $input['next_manager'], $input['current_manager'] ?? null);
            DB::commit();
            return $this->sendResponse(array('success' => true, 'message' => null), 'Success change or assign head branch');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError(array('success' => false, 'message' => $err->getMessage()), defaultResponseError($err->getMessage()));
        }
    }
}
