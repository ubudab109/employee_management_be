<?php

namespace App\Repositories\CompanyDivision;

interface CompanyDivisionInterface
{
  public function getAllDivision($branchId, $keyword);
  public function getPaginateDivision($branchId, $keyword, $show);
  public function detailDivision($id);
  public function storeDivision(array $data);
  public function updateDivision(array $data, $id);
  public function deleteDivision($id);
}