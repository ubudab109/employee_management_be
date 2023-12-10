<?php

namespace App\Repositories\CompanyBranch;

use App\Models\CompanyBranch;
use App\Models\UserManagerAssign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyBranchRepository implements CompanyBranchInterface
{
    /**
     * @var ModelName
     */
    protected $model;

    public function __construct(CompanyBranch $model)
    {
        $this->model = $model;
    }

    /**
     * Get All Branch Without Paginate
     * @param string $keyword - keyword by branch name or address
     * @param int $province - filter by province id
     * @param int $regency - filter by regency id
     * @param int $district - filter by district id
     * @param int $villages - filter by villages id
     * @param int $status - filter by status
     * @return \App\Models\CompanyBranch
     */
    public function getAllBranch($keyword, $province, $regency, $district, $villages, $status)
    {
        return $this->model
            ->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
                $query->where('branch_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('branch_code', 'LIKE', '%'. $keyword. '%')
                    ->orWhere('address', 'LIKE', '%' . $keyword . '%');
            })
            ->when($province != null, function ($query) use ($province) {
                $query->where('province_id', $province);
            })
            ->when($regency != null, function ($query) use ($regency) {
                $query->where('regency_id', $regency);
            })
            ->when($district != null, function ($query) use ($district) {
                $query->where('district_id', $district);
            })
            ->when($villages != null, function ($query) use ($villages) {
                $query->where('villages_id', $villages);
            })
            ->when($status != null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('branch_order')
            ->get();
    }

    /**
     * Get All Branch With Paginate
     * @param string $keyword - keyword by branch name or address
     * @param int $province - filter by province id
     * @param int $regency - filter by regency id
     * @param int $district - filter by district id
     * @param int $villages - filter by villages id
     * @param int $status - filter by status
     * @param int $show - show data per page
     * @return \App\Models\CompanyBranch
     */
    public function getPaginateBranch($keyword, $province, $regency, $district, $villages, $status, $show)
    {
        return $this->model
            ->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
                $query->where('branch_name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('address', 'LIKE', '%' . $keyword . '%');
            })
            ->when($province != null, function ($query) use ($province) {
                $query->where('province_id', $province);
            })
            ->when($regency != null, function ($query) use ($regency) {
                $query->where('regency_id', $regency);
            })
            ->when($district != null, function ($query) use ($district) {
                $query->where('district_id', $district);
            })
            ->when($villages != null, function ($query) use ($villages) {
                $query->where('villages_id', $villages);
            })
            ->when($status != null, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('branch_order')
            ->paginate($show);
    }

    /**
     * Get Detail Branch By ID
     * @param int $id - ID Company Branch
     * @return array
     */
    public function detailBranch($id)
    {
        $headBranch = DB::table('user_manager_assign as manager')
        ->select('user_manager.id', 'user_manager.name', 'user_manager.email', 'user_manager.profile_picture')
        ->join('model_has_roles as mr', 'mr.model_id', 'manager.id')
        ->join('roles', 'roles.id', 'mr.role_id')
        ->join('user_manager', 'user_manager.id', 'manager.user_manager_id')
        ->where('roles.is_headbranch', 1)
        ->where('mr.model_type', UserManagerAssign::class)
        ->where('manager.branch_id', $id)
        ->first();

        $branch = $this->model
        ->with(['provincies:id,name', 'regencies:id,name', 'district:id,name', 'villages:id,name'])
        ->findOrFail($id);

        return [
            'head_branch' => $headBranch,
            'branch'      => $branch,
        ];

    }

    /**
     * Create New Branch
     * @param array $data - Data to save
     * @return \App\Models\CompanyBranch
     */
    public function createBranch(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update Branch By ID
     * @param int $id - ID Company Branch
     * @param array $data - Data to save
     * @return boolean
     */
    public function updateBranch($id, array $data)
    {
        $branch = $this->model->findOrFail($id);
        return $branch->update($data);
    }

    /**
     * Delete Branch By ID
     * @param int $id - ID Company Branch
     * @return boolean
     */
    public function deleteBranch($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * Validating branch code
     * 
     * @param string $code - branch code
     * @return boolean
     */
    public function validateBranchCode($code)
    {
        $branch = $this->model->where('branch_code', $code)->exists();
        if ($branch) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get Latest Branch
     * 
     * @return int
     */
    public function getLastBranch()
    {
        $branch = $this->model->orderBy('branch_order', 'desc')->first();
        if ($branch != null) {
            return $branch->branch_order;
        } else {
            return (int)0;
        }
    }

    /**
     * Get First Branch By Condition
     * 
     * @param array $condition - array of condition
     * @return \App\Models\CompanyBranch
     */
    public function getFirstBranchByCondition(array $condition)
    {
        return $this->model->where($condition)->first();
    }

    /**
     * Assign or change headbranch based on branch
     * @param string $type - type of assign (assign or change)
     * @param int $branchId - branchID
     * @param int $nextHeadBranch - User manager who want to be a head branch
     * @param int $currentHeadBranch - Current head branch
     * @return bool
     */
    public function assignOrChangeHeadBranch(string $type, int $branchId, int $nextHeadBreanch, int $currentHeadbranch = null)
    {
        $branch = $this->model->find($branchId);
        $headBranchRole = $branch->roles()->where('is_headbranch', true)->first();
        if ($type == ASSIGN_HEADBRANCH) {
            $assign = $branch->managerAssign()->where('user_manager_id', $nextHeadBreanch)->first();
            if (!$assign) {
                return false;
            }
            $assign->assignRole($headBranchRole);
        } else if ($type == CHANGE_HEADBRANCH) {
            $currentManager = $this->model->managerAssign()->where('user_manager_id', $currentHeadbranch)->first();
            $nextManager = $this->model->managerAssign()->where('user_manager_id', $nextHeadBreanch)->first();
            $currentRoleManager = $currentManager->roles()->first();
            $nextRoleManager = $nextManager->roles()->first();
            $currentManager->assignRole($nextRoleManager->id);
            $nextManager->assignRole($currentRoleManager->id);
        }
        return true;
    }
}
