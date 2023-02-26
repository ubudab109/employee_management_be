<?php

namespace App\Repositories\CompanyBranch;

use App\Models\CompanyBranch;

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
     * @return \App\Models\CompanyBranch
     */
    public function detailBranch($id)
    {
        return $this->model->findOrFail($id);
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
}
