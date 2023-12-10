<?php

namespace App\Repositories\CompanyBranch;

interface CompanyBranchInterface
{
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
  public function getAllBranch($keyword, $province, $regency, $district, $villages, $status);

  /**
   * Get All Branch With Paginate
   * @param string $keyword - keyword by branch name or address
   * @param int $province - filter by province id
   * @param int $regency - filter by regency id
   * @param int $district - filter by district id
   * @param int $villages - filter by villages id
   * @param int $status - filter by status
   * @param int $show - show data per page
   */
  public function getPaginateBranch($keyword, $province, $regency, $district, $villages, $status, $show);

  /**
   * Get Detail Branch By ID
   * @param int $id - ID Company Branch
   */
  public function detailBranch($id);

  /**
   * Create New Branch
   * @param array $data - Data to save
   */
  public function createBranch(array $data);

  /**
   * Update Branch By ID
   * @param int $id - ID Company Branch
   * @param array $data - Data to save
   */
  public function updateBranch($id, array $data);

  /**
   * Delete Branch By ID
   * @param int $id - ID Company Branch
   */
  public function deleteBranch($id);

  /**
   * Validating branch code
   * 
   * @param string $code - branch code
   */
  public function validateBranchCode($code);

  /**
   * Get Latest Branch
   * 
   */
  public function getLastBranch();

  /**
   * Get First Branch By Condition
   * 
   * @param array $condition - array of condition
   */
  public function getFirstBranchByCondition(array $condition);

  /**
   * Assign or change headbranch based on branch
   * @param string $type - type of assign (assign or change)
   * @param int $branchId - branchID
   * @param int $nextHeadBranch - User manager who want to be a head branch
   * @param int $currentHeadBranch - Current head branch
   * @return bool
   */
  public function assignOrChangeHeadBranch(string $type, int $branchId, int $nextHeadBreanch, int $currentHeadbranch = null);
}
