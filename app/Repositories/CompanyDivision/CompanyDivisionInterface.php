<?php

namespace App\Repositories\CompanyDivision;

interface CompanyDivisionInterface
{
  /**
   * Get All Without Paginate Division
   * 
   * @param string $keyword
   * @return \App\Models\CompanyDivision
   */
  public function getAllDivision($branchId, $keyword);

  /**
   * Get Data Division With Paginate
   * 
   * @param string $keyword
   * @param int $show
   * @return \App\Model\CompanyDivision
   */
  public function getPaginateDivision($branchId, $keyword, $show);

  /**
   * Get Detail Division By Id
   * 
   * @param int $id
   * @return \App\Models\CompanyDivision
   */
  public function detailDivision($id);

  /**
   * Store New Division Data
   * 
   * @param array $data
   * @return \App\Models\CompanyDivision
   */
  public function storeDivision(array $data);

  /**
   * Update Existing Division Data By ID
   * 
   * @param array $data
   * @param int $id
   * @return \App\Models\CompanyDivision
   */
  public function updateDivision(array $data, $id);

  /**
   * Delete Existing Division Data By ID
   * 
   * @param int $id
   * @return boolean
   */
  public function deleteDivision($id);
}
