<?php

namespace App\Repositories\CompanyDivision;

interface CompanyDivisionInterface
{
  public function getAllDivision($keyword);
  public function getPaginateDivision($keyword, $show);
  public function detailDivision($id);
  public function storeDivision(array $data);
  public function updateDivision(array $data, $id);
  public function deleteDivision($id);
}