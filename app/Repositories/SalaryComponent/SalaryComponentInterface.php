<?php

namespace App\Repositories\SalaryComponent;

interface SalaryComponentInterface
{

  public function listSalaryComponent($keyword, $type);
  public function createSalaryComponent(array $data);
  public function updateSalaryComponent(array $data, $id);

}